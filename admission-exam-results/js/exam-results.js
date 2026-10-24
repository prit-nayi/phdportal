/* ============================================================
   exam-results.js – PhD Portal | Admission Exam Results Management
   Handles: Batch-wise marks entry, passing marks validation,
   auto-pass calculation with manual override, keyboard navigation
   (Tab & Enter skipping checkboxes), and AJAX saving.
   ============================================================ */

"use strict";

let formDirty = false;
let submitting = false;
let currentExamData = null;
let studentsState = [];
let currentActiveTab = "written";

let passingWrittenMarks = null;
let passingPresentationMarks = null;

function url(path) {
  if (typeof window.__APP__ !== "undefined" && window.__APP__.baseUrl) {
    return window.__APP__.baseUrl + path;
  }
  const baseDir = window.location.pathname.split("/admission-exam-results")[0];
  return baseDir + path;
}

$(document).ready(function () {
  // Activate Bootstrap tooltips
  $('[data-toggle="tooltip"]').tooltip();

  // Tab change event handlers
  $('#exam-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    const target = $(e.target).attr('href');
    if (target === '#written-tab') {
      currentActiveTab = 'written';
      $('#save-btn-label').text('Save Written Exam Marks');
      setTimeout(() => { $('#written-tab .written-marks-input:first').focus().select(); }, 50);
    } else if (target === '#presentation-tab') {
      currentActiveTab = 'presentation';
      $('#save-btn-label').text('Save Presentation Marks');
      setTimeout(() => { $('#presentation-tab .pres-marks-input:first').focus().select(); }, 50);
    } else if (target === '#ppt-tab') {
      currentActiveTab = 'ppt';
      $('#save-btn-label').text('Save PPT Files');
    }
  });

  // Admission Exam selection change
  $('#examId').on('change', function () {
    const examId = $(this).val();
    if (!examId || parseInt(examId) <= 0) {
      $('#students-container').hide();
      return;
    }
    loadBatchStudents(examId);
  });

  // Handle ENTER key progression on marks input fields
  $(document).on('keydown', '.written-marks-input, .pres-marks-input', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      const currentIndex = parseInt($(this).attr('data-index'));
      const nextIndex = currentIndex + 1;
      const $activePane = $(this).closest('.tab-pane');
      const $nextInput = $activePane.find(`input[data-index="${nextIndex}"]`);

      if ($nextInput.length > 0) {
        $nextInput.focus().select();
      }
    }
  });

  // Warn before leaving if dirty
  window.addEventListener("beforeunload", function (e) {
    if (formDirty && !submitting) {
      e.preventDefault();
      e.returnValue = "";
    }
  });
});

/**
 * Fetch students for the selected admission exam batch
 */
function loadBatchStudents(examId) {
  $('#students-container').show();
  $('#loading-state').show();
  $('#empty-state').hide();
  $('#written-table-body, #presentation-table-body').empty();
  $('#total-students-count').text('0 Students');

  passingWrittenMarks = null;
  passingPresentationMarks = null;
  $('#passingWrittenMarks').val('').removeClass('is-invalid');
  $('#passingPresentationMarks').val('').removeClass('is-invalid');
  $('.err-passing-written, .err-passing-pres').text('').hide();

  $.ajax({
    method: "GET",
    url: url("/admission-exam-results/api/get-students?examId=" + encodeURIComponent(examId)),
    dataType: "json",
    success: function (res) {
      $('#loading-state').hide();
      if (!res.data || !res.data.exam) {
        showAlert("Failed to load exam data.", "danger");
        return;
      }

      currentExamData = res.data.exam;
      const rawStudents = res.data.students || [];

      // Map studentsState with userOverriddenPassed flag and PPT fields
      studentsState = rawStudents.map(s => ({
        studentId: s.studentId,
        studentName: s.studentName,
        applicationNo: s.applicationNo,
        enrollmentNo: s.enrollmentNo || s.applicationNo || '—',
        writtenMarks: s.writtenMarks !== null ? s.writtenMarks : null,
        presentationMarks: s.presentationMarks !== null ? s.presentationMarks : null,
        hasPassed: s.hasPassed !== null ? s.hasPassed : 0,
        presentationPptFile: s.presentationPptFile || null,
        presentationPptUrl: s.presentationPptUrl || null,
        userOverriddenPassed: false
      }));

      // Update Max Marks headers
      const maxW = currentExamData.maxWritten !== null ? currentExamData.maxWritten : "--";
      const maxP = currentExamData.maxPresentation !== null ? currentExamData.maxPresentation : "--";

      $('.max-written-val').text(maxW);
      $('.max-pres-val').text(maxP);
      $('.max-written-header').text(`Max: ${maxW}`);
      $('.max-pres-header').text(`Max: ${maxP}`);

      if (studentsState.length === 0) {
        $('#empty-state').show();
        return;
      }

      $('#total-students-count').text(`${studentsState.length} Student${studentsState.length === 1 ? '' : 's'}`);
      renderTables();
      if (typeof renderPptTable === 'function') {
        renderPptTable();
      }

      // Auto-focus first input box
      setTimeout(() => {
        $('#written-tab .written-marks-input:first').focus().select();
      }, 100);
    },
    error: function (xhr) {
      $('#loading-state').hide();
      showAlert(extractErrorMessage(xhr), "danger");
    }
  });
}

/**
 * Render Written and Presentation student tables
 */
function renderTables() {
  const $writtenTbody = $('#written-table-body').empty();
  const $presTbody = $('#presentation-table-body').empty();

  const maxW = currentExamData.maxWritten !== null ? currentExamData.maxWritten : null;
  const maxP = currentExamData.maxPresentation !== null ? currentExamData.maxPresentation : null;

  studentsState.forEach((stu, index) => {
    const srNo = index + 1;
    const wMarks = stu.writtenMarks !== null ? stu.writtenMarks : "";
    const pMarks = stu.presentationMarks !== null ? stu.presentationMarks : "";
    const isPassed = stu.hasPassed == 1;

    // Written Row (tabindex="-1" on checkbox so keyboard navigation skips checkboxes)
    const wRow = `
      <tr id="w-row-${stu.studentId}">
        <td class="align-middle font-weight-bold">${srNo}</td>
        <td class="align-middle text-muted">${escapeHtml(stu.applicationNo)}</td>
        <td class="align-middle text-muted">${escapeHtml(stu.enrollmentNo)}</td>
        <td class="align-middle font-weight-bold text-dark">${escapeHtml(stu.studentName)}</td>
        <td class="align-middle">
          <input type="number" step="0.01" min="0" ${maxW !== null ? 'max="' + maxW + '"' : ''}
                 class="form-control written-marks-input"
                 data-student-id="${stu.studentId}"
                 data-index="${index}"
                 placeholder="e.g. 75.50"
                 value="${wMarks}"
                 oninput="onMarksChange(${stu.studentId}, 'written', this.value, ${index});">
          <div class="invalid-feedback err-written-${stu.studentId}"></div>
        </td>
        <td class="align-middle">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" tabindex="-1" class="custom-control-input pass-checkbox pass-cb-${stu.studentId}"
                   id="pass-w-${stu.studentId}" data-student-id="${stu.studentId}"
                   ${isPassed ? 'checked' : ''}
                   onchange="onPassToggle(${stu.studentId}, this.checked);">
            <label class="custom-control-label font-weight-bold text-success" for="pass-w-${stu.studentId}">Passed</label>
          </div>
        </td>
      </tr>
    `;
    $writtenTbody.append(wRow);

    // Presentation Row (tabindex="-1" on checkbox so keyboard navigation skips checkboxes)
    const pRow = `
      <tr id="p-row-${stu.studentId}">
        <td class="align-middle font-weight-bold">${srNo}</td>
        <td class="align-middle text-muted">${escapeHtml(stu.applicationNo)}</td>
        <td class="align-middle text-muted">${escapeHtml(stu.enrollmentNo)}</td>
        <td class="align-middle font-weight-bold text-dark">${escapeHtml(stu.studentName)}</td>
        <td class="align-middle">
          <input type="number" step="0.01" min="0" ${maxP !== null ? 'max="' + maxP + '"' : ''}
                 class="form-control pres-marks-input"
                 data-student-id="${stu.studentId}"
                 data-index="${index}"
                 placeholder="e.g. 75.50"
                 value="${pMarks}"
                 oninput="onMarksChange(${stu.studentId}, 'presentation', this.value, ${index});">
          <div class="invalid-feedback err-pres-${stu.studentId}"></div>
        </td>
        <td class="align-middle">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" tabindex="-1" class="custom-control-input pass-checkbox pass-cb-${stu.studentId}"
                   id="pass-p-${stu.studentId}" data-student-id="${stu.studentId}"
                   ${isPassed ? 'checked' : ''}
                   onchange="onPassToggle(${stu.studentId}, this.checked);">
            <label class="custom-control-label font-weight-bold text-success" for="pass-p-${stu.studentId}">Passed</label>
          </div>
        </td>
      </tr>
    `;
    $presTbody.append(pRow);
  });
}

/**
 * Handle Passing Marks Change
 */
function onPassingMarksChange(type, valStr) {
  formDirty = true;
  const maxVal = (type === 'written')
    ? (currentExamData.maxWritten !== null ? currentExamData.maxWritten : null)
    : (currentExamData.maxPresentation !== null ? currentExamData.maxPresentation : null);

  const $input = (type === 'written') ? $('#passingWrittenMarks') : $('#passingPresentationMarks');
  const $err = (type === 'written') ? $('.err-passing-written') : $('.err-passing-pres');

  $input.removeClass('is-invalid is-invalid-mark');
  $err.text('').hide();

  if (valStr !== '' && valStr !== null) {
    const val = parseFloat(valStr);
    if (isNaN(val) || val < 0) {
      if (type === 'written') passingWrittenMarks = null;
      else passingPresentationMarks = null;
      $input.addClass('is-invalid is-invalid-mark');
      $err.text('Passing marks must be >= 0.').show();
      return;
    }
    if (maxVal !== null && val > maxVal) {
      if (type === 'written') passingWrittenMarks = null;
      else passingPresentationMarks = null;
      $input.addClass('is-invalid is-invalid-mark');
      $err.text(`Passing marks cannot exceed Max (${maxVal}).`).show();
      return;
    }

    if (type === 'written') passingWrittenMarks = val;
    else passingPresentationMarks = val;
  } else {
    if (type === 'written') passingWrittenMarks = null;
    else passingPresentationMarks = null;
  }

  // Recalculate pass checkboxes for all students in this tab if not manually overridden
  recalculateAutoPass(type);
}

/**
 * Recalculate auto-pass for students in the specified tab
 */
function recalculateAutoPass(type) {
  const passingMarks = (type === 'written') ? passingWrittenMarks : passingPresentationMarks;

  studentsState.forEach(stu => {
    // If user has NOT manually toggled the checkbox, auto-calculate
    if (!stu.userOverriddenPassed && passingMarks !== null) {
      const markVal = (type === 'written') ? stu.writtenMarks : stu.presentationMarks;
      if (markVal !== null) {
        const isPassed = markVal >= passingMarks;
        stu.hasPassed = isPassed ? 1 : 0;
        $(`.pass-cb-${stu.studentId}`).prop('checked', isPassed);
      }
    }
  });
}

/**
 * Handle student marks input change and validation
 */
function onMarksChange(studentId, type, valStr, index) {
  formDirty = true;
  const stu = studentsState.find(s => s.studentId === studentId);
  if (!stu) return;

  const maxVal = (type === 'written')
    ? (currentExamData.maxWritten !== null ? currentExamData.maxWritten : null)
    : (currentExamData.maxPresentation !== null ? currentExamData.maxPresentation : null);

  const $input = (type === 'written')
    ? $(`#w-row-${studentId} .written-marks-input`)
    : $(`#p-row-${studentId} .pres-marks-input`);

  const $err = (type === 'written')
    ? $(`.err-written-${studentId}`)
    : $(`.err-pres-${studentId}`);

  $input.removeClass('is-invalid is-invalid-mark');
  $err.text('').hide();

  if (valStr !== '' && valStr !== null) {
    const val = parseFloat(valStr);
    if (isNaN(val) || val < 0) {
      stu[type === 'written' ? 'writtenMarks' : 'presentationMarks'] = null;
      $input.addClass('is-invalid is-invalid-mark');
      $err.text('Marks must be >= 0.').show();
      return;
    }

    if (maxVal !== null && val > maxVal) {
      stu[type === 'written' ? 'writtenMarks' : 'presentationMarks'] = val;
      $input.addClass('is-invalid is-invalid-mark');
      $err.text(`Cannot exceed Max (${maxVal})`).show();
      return;
    }

    stu[type === 'written' ? 'writtenMarks' : 'presentationMarks'] = val;

    // Auto-calculate passed status if not manually overridden by user
    const passingMarks = (type === 'written') ? passingWrittenMarks : passingPresentationMarks;
    if (!stu.userOverriddenPassed && passingMarks !== null) {
      const isPassed = val >= passingMarks;
      stu.hasPassed = isPassed ? 1 : 0;
      $(`.pass-cb-${studentId}`).prop('checked', isPassed);
    }
  } else {
    stu[type === 'written' ? 'writtenMarks' : 'presentationMarks'] = null;
  }
}

/**
 * Synchronize "Passed" checkbox across both tabs and set user override flag
 */
function onPassToggle(studentId, isChecked) {
  formDirty = true;
  const stu = studentsState.find(s => s.studentId === studentId);
  if (stu) {
    stu.hasPassed = isChecked ? 1 : 0;
    stu.userOverriddenPassed = true; // Mark as manually overridden by user
  }
  // Keep both checkboxes in sync
  $(`.pass-cb-${studentId}`).prop('checked', isChecked);
}

/**
 * Save Active Tab Marks (Written or Presentation)
 */
function saveActiveTabMarks() {
  if (currentActiveTab === 'ppt') {
    if (typeof savePresentationPptFiles === 'function') {
      savePresentationPptFiles();
    }
    return;
  }

  const examId = $('#examId').val();
  if (!examId || parseInt(examId) <= 0) {
    showAlert("Please select an Admission Exam.", "danger");
    return;
  }

  if (studentsState.length === 0) {
    showAlert("No students to save.", "warning");
    return;
  }

  const type = currentActiveTab;
  const passingVal = (type === 'written') ? passingWrittenMarks : passingPresentationMarks;
  const $passingInput = (type === 'written') ? $('#passingWrittenMarks') : $('#passingPresentationMarks');

  // Validate Passing Marks field (Mandatory)
  if (passingVal === null || passingVal === undefined || $passingInput.val().trim() === '') {
    $passingInput.addClass('is-invalid is-invalid-mark');
    showAlert(`Please enter valid Passing Marks for the ${type === 'written' ? 'Written' : 'Presentation'} Exam.`, "danger");
    $passingInput.focus();
    return;
  }

  // Validate active tab input fields
  let hasError = false;
  const maxVal = (type === 'written')
    ? (currentExamData.maxWritten !== null ? currentExamData.maxWritten : null)
    : (currentExamData.maxPresentation !== null ? currentExamData.maxPresentation : null);

  studentsState.forEach(stu => {
    const val = (type === 'written') ? stu.writtenMarks : stu.presentationMarks;
    if (val !== null && val !== '') {
      if (val < 0 || (maxVal !== null && val > maxVal)) {
        hasError = true;
      }
    }
  });

  if (hasError) {
    showAlert("Please fix validation errors highlighted in red before saving.", "danger");
    return;
  }

  // Prepare payload
  const payloadStudents = studentsState.map(stu => ({
    studentId: stu.studentId,
    marks: (type === 'written') ? stu.writtenMarks : stu.presentationMarks,
    hasPassed: stu.hasPassed
  }));

  const payload = {
    examId: parseInt(examId),
    tab: type,
    passingMarks: passingVal,
    students: payloadStudents
  };

  submitting = true;
  lockSubmit();

  $.ajax({
    method: "POST",
    url: url("/admission-exam-results/api/batch-save"),
    contentType: "application/json",
    data: JSON.stringify(payload),
    success: function (res) {
      formDirty = false;
      submitting = false;
      unlockSubmit();
      showAlert(res.message || "Exam marks saved successfully.", "success");
    },
    error: function (xhr) {
      submitting = false;
      unlockSubmit();
      showAlert(extractErrorMessage(xhr), "danger");
    }
  });
}

// ── Alert & Button Helpers ───────────────────────────────────────────────────

function showAlert(message, type) {
  const iconMap = {
    danger: "fas fa-times-circle",
    warning: "fas fa-exclamation-triangle",
    success: "fas fa-check-circle",
  };
  const icon = iconMap[type] || "fas fa-info-circle";
  const html = `
    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
      <i class="${icon} mr-2"></i>${message}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>`;
  const $el = $("#form-alert");
  $el.html(html).show();
  $("html, body").animate({ scrollTop: $el.offset().top - 80 }, 300);
}

function showFlashAlert(message, type) {
  const iconMap = {
    danger: "fas fa-times-circle",
    warning: "fas fa-exclamation-triangle",
    success: "fas fa-check-circle",
  };
  const icon = iconMap[type] || "fas fa-info-circle";
  const html = `
    <div class="alert alert-${type} alert-dismissible fade show text-left" role="alert">
      <i class="${icon} mr-2"></i>${message}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>`;
  const $el = $("#flash-alert");
  $el.html(html).show();
  $("html, body").animate({ scrollTop: 0 }, 300);
}

function lockSubmit() {
  $(".btn-submit").attr("disabled", "");
  $(".btn-submit-spinner").show();
}

function unlockSubmit() {
  $(".btn-submit").removeAttr("disabled");
  $(".btn-submit-spinner").hide();
}

function extractErrorMessage(xhr) {
  try {
    const json = JSON.parse(xhr.responseText);
    if (json && json.message) return json.message;
  } catch (_) {}
  return xhr.responseText || "An unexpected error occurred. Please try again.";
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// ── Backward Compatibility Handlers for Edit & Delete ───────────────────────

function editResult(eventOrId, event) {
  let e = event;
  let idFromArg = null;

  if (eventOrId && typeof eventOrId.preventDefault === "function") {
    e = eventOrId;
  } else if (typeof eventOrId === "number" || typeof eventOrId === "string") {
    idFromArg = eventOrId;
  }

  if (e && typeof e.preventDefault === "function") {
    e.preventDefault();
  }

  const formElement = document.getElementById("result-form");
  const formData = new FormData(formElement);

  const urlParams = new URLSearchParams(window.location.search);
  const resultId = idFromArg || $("#result-form input[name='id']").val() || urlParams.get("id");
  if (resultId && !formData.has("id")) {
    formData.append("id", resultId);
  }

  submitting = true;
  lockSubmit();

  $.ajax({
    method: "POST",
    url: url("/admission-exam-results/api/edit?id=" + encodeURIComponent(resultId)),
    contentType: false,
    processData: false,
    data: formData,
    success: function (res) {
      formDirty = false;
      window.location.href = url(
        "/admission-exam-results?msg=" +
        encodeURIComponent(res.message || "Exam result updated successfully.") +
        "&type=success"
      );
    },
    error: function (xhr) {
      submitting = false;
      unlockSubmit();
      showAlert(extractErrorMessage(xhr), "danger");
    },
  });

  return false;
}

let pendingDeleteId = null;

function confirmDelete(id, studentName) {
  pendingDeleteId = id;
  $("#deleteStudentName").text(studentName);
  $("#deleteModal").modal("show");
}

function deleteResult() {
  if (!pendingDeleteId) return;

  const $btn = $("#confirmDeleteBtn");
  $btn.attr("disabled", "").text("Deleting...");

  $.ajax({
    method: "POST",
    url: url("/admission-exam-results/api/delete"),
    data: { id: pendingDeleteId },
    dataType: "json",
    success: function (res) {
      $("#deleteModal").modal("hide");
      showFlashAlert(res.message || "Result deleted successfully.", "success");
      $(`#result-row-${pendingDeleteId}`).fadeOut(400, function () {
        $(this).remove();
      });
      pendingDeleteId = null;
    },
    error: function (xhr) {
      $("#deleteModal").modal("hide");
      $btn.removeAttr("disabled").text("Delete");
      showFlashAlert(extractErrorMessage(xhr), "danger");
      pendingDeleteId = null;
    },
  });
}
