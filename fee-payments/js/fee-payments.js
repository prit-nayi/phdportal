/* ============================================================
   fee-payments.js  –  PhD Portal | Fee Payments Management
   Handles: client validation for cheque payments, AJAX add/edit/delete,
   unsaved-changes warning, loading indicators.
   ============================================================ */

"use strict";

let formDirty = false;
let submitting = false;

$(document).ready(function () {
  // Initialize Searchable Student Dropdown if present
  initSearchableStudentDropdown();

  // Mark form dirty when any input changes
  $("#payment-form input, #payment-form select, #payment-form textarea").on("input change", function () {
    formDirty = true;
  });

  // Warn before leaving page with unsaved changes
  window.addEventListener("beforeunload", function (e) {
    if (formDirty && !submitting) {
      e.preventDefault();
      e.returnValue = "";
    }
  });

  // Activate Bootstrap tooltips on list/index page
  $('[data-toggle="tooltip"]').tooltip();
});

/**
 * Show a Bootstrap alert message inside #form-alert.
 */
function showAlert(message, type) {
  const iconMap = {
    danger:  "fas fa-times-circle",
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
  if ($el.length && $el.offset()) {
    $("html, body").animate({ scrollTop: $el.offset().top - 80 }, 300);
  }
}

/**
 * Show a Bootstrap alert message inside #flash-alert (index page).
 */
function showFlashAlert(message, type) {
  const iconMap = {
    danger:  "fas fa-times-circle",
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
  if ($el.length && $el.offset()) {
    $("html, body").animate({ scrollTop: $el.offset().top - 80 }, 300);
  }
}

/** Check file extension */
function isValidFileExtension(fileName) {
  if (!fileName) return true;
  const allowed = ["pdf", "jpg", "jpeg", "png"];
  const ext = fileName.split('.').pop().toLowerCase();
  return allowed.includes(ext);
}

/**
 * Helper to escape HTML characters for safe rendering
 */
function escapeHtml(str) {
  if (!str) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

/**
 * Initialize searchable Student dropdown
 */
function initSearchableStudentDropdown() {
  const $wrapper = $("#student-select-wrapper");
  if (!$wrapper.length) return;

  const $hiddenInput = $("#studentId");
  const $trigger = $("#studentId-trigger");
  const $displayText = $("#studentId-display-text");
  const $menu = $("#studentId-menu");
  const $searchInput = $("#studentId-search");
  const $clearBtnContainer = $("#studentId-search-clear");
  const $clearBtn = $("#btn-clear-search");
  const $list = $("#studentId-list");

  const students = window.__STUDENTS__ || [];
  let selectedStudentId = window.__SELECTED_STUDENT_ID__ || $hiddenInput.val() || null;
  let focusedIndex = -1;

  function formatStudentLabel(student) {
    if (!student) return "-- Select Student --";
    const name = `${student.StudentName || ''} ${student.Surname || ''}`.trim();
    return `${name} - ${student.ApplicationNo || ''}`;
  }

  function renderList(filterQuery = "") {
    const query = filterQuery.toLowerCase().trim();
    const selectedFeeType = $("#feeType").val();
    $list.empty();
    focusedIndex = -1;

    if (students.length === 0) {
      $list.append('<div class="searchable-select-empty">No students available</div>');
      return;
    }

    // Eligibility filter: exclude students who have already paid for the selected fee type
    const eligibleStudents = students.filter(function (s) {
      if (!selectedFeeType) return true;
      if (s.paidFeeTypes && Array.isArray(s.paidFeeTypes) && s.paidFeeTypes.includes(selectedFeeType)) {
        return false;
      }
      return true;
    });

    if (eligibleStudents.length === 0) {
      $list.append('<div class="searchable-select-empty">No students available for this fee type.</div>');
      return;
    }

    const filtered = eligibleStudents.filter(function (s) {
      if (!query) return true;

      const fullName = `${s.StudentName || ''} ${s.Surname || ''}`.toLowerCase();
      const appNo = (s.ApplicationNo || '').toLowerCase();
      const studentId = String(s.id || '').toLowerCase();
      const batchName = (s.batchName || '').toLowerCase();
      const enrollmentYear = String(s.enrollmentYear || '').toLowerCase();
      const email = (s.EmailStudent || '').toLowerCase();

      return (
        fullName.includes(query) ||
        appNo.includes(query) ||
        studentId === query ||
        studentId.includes(query) ||
        batchName.includes(query) ||
        enrollmentYear.includes(query) ||
        email.includes(query)
      );
    });

    if (filtered.length === 0) {
      $list.append('<div class="searchable-select-empty">No students found</div>');
      return;
    }

    filtered.forEach(function (s) {
      const isSelected = String(s.id) === String(selectedStudentId);
      const fullName = `${s.StudentName || ''} ${s.Surname || ''}`.trim();
      let batchText = '';
      if (s.batchName) {
        batchText = ` | Batch: ${s.batchName}${s.enrollmentYear ? ' (' + s.enrollmentYear + ')' : ''}`;
      }

      const itemHtml = `
        <div class="searchable-select-item ${isSelected ? 'is-selected' : ''}" data-id="${s.id}" role="option" aria-selected="${isSelected}">
          <div class="student-title font-weight-bold">${escapeHtml(fullName)}</div>
          <div class="student-meta">Application No: ${escapeHtml(s.ApplicationNo || 'N/A')}${escapeHtml(batchText)}</div>
        </div>
      `;
      $list.append(itemHtml);
    });
  }

  // Handle Fee Type changes: re-evaluate eligibility and clear selection if currently selected student has already paid
  $("#feeType").off("change.studentFilter").on("change.studentFilter", function () {
    const selectedFeeType = $(this).val();
    if (selectedStudentId && selectedFeeType) {
      const currentStudent = students.find(function (s) {
        return String(s.id) === String(selectedStudentId);
      });
      if (currentStudent && currentStudent.paidFeeTypes && Array.isArray(currentStudent.paidFeeTypes) && currentStudent.paidFeeTypes.includes(selectedFeeType)) {
        selectStudent(null);
      }
    }
    if ($menu.is(":visible")) {
      renderList($searchInput.val());
    }
  });

  function selectStudent(studentId) {
    selectedStudentId = studentId;
    $hiddenInput.val(studentId ? studentId : '').trigger('change');

    const selectedStudent = students.find(function (s) {
      return String(s.id) === String(studentId);
    });

    if (selectedStudent) {
      $displayText.text(formatStudentLabel(selectedStudent)).removeClass('text-muted');
    } else {
      $displayText.text('-- Select Student --').addClass('text-muted');
    }

    $wrapper.removeClass('is-invalid');
    closeDropdown();
  }

  function openDropdown() {
    $menu.show();
    $wrapper.addClass('is-open');
    $trigger.attr('aria-expanded', 'true');
    renderList($searchInput.val());
    setTimeout(function () {
      $searchInput.focus();
    }, 50);
  }

  function closeDropdown() {
    $menu.hide();
    $wrapper.removeClass('is-open');
    $trigger.attr('aria-expanded', 'false');
    focusedIndex = -1;
  }

  // Set initial selected display
  if (selectedStudentId) {
    selectStudent(selectedStudentId);
  } else {
    $displayText.text('-- Select Student --');
  }

  // Trigger click / key press
  $trigger.on('click', function (e) {
    e.preventDefault();
    if ($menu.is(':visible')) {
      closeDropdown();
    } else {
      openDropdown();
    }
  });

  $trigger.on('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
      e.preventDefault();
      openDropdown();
    }
  });

  // Search input events
  $searchInput.on('input', function () {
    const val = $(this).val();
    if (val.trim() !== '') {
      $clearBtnContainer.show();
    } else {
      $clearBtnContainer.hide();
    }
    renderList(val);
  });

  $searchInput.on('click', function (e) {
    e.stopPropagation();
  });

  $clearBtn.on('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    $searchInput.val('');
    $clearBtnContainer.hide();
    renderList('');
    $searchInput.focus();
  });

  // Option selection click
  $list.on('click', '.searchable-select-item', function (e) {
    e.stopPropagation();
    const id = $(this).data('id');
    selectStudent(id);
  });

  // Keyboard navigation inside list/search
  $wrapper.on('keydown', function (e) {
    if (!$menu.is(':visible')) return;

    const $items = $list.find('.searchable-select-item');
    if (!$items.length) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      focusedIndex = (focusedIndex + 1) % $items.length;
      $items.removeClass('is-focused');
      const $current = $items.eq(focusedIndex).addClass('is-focused');
      if ($current.length) {
        $current[0].scrollIntoView({ block: 'nearest' });
      }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      focusedIndex = (focusedIndex - 1 + $items.length) % $items.length;
      $items.removeClass('is-focused');
      const $current = $items.eq(focusedIndex).addClass('is-focused');
      if ($current.length) {
        $current[0].scrollIntoView({ block: 'nearest' });
      }
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (focusedIndex >= 0 && focusedIndex < $items.length) {
        const id = $items.eq(focusedIndex).data('id');
        selectStudent(id);
      }
    } else if (e.key === 'Escape') {
      e.preventDefault();
      closeDropdown();
      $trigger.focus();
    }
  });

  // Click outside to close
  $(document).off('click.studentDropdown').on('click.studentDropdown', function (e) {
    if (!$(e.target).closest('#student-select-wrapper').length) {
      closeDropdown();
    }
  });
}

/** Client-side form validation for Cheque Payments */
function validatePaymentForm() {
  const studentId = $("#studentId").val();
  const feeType = $("#feeType").val();
  const paidOnDate = $("#paidOnDate").val();
  const chequeNumber = $("#chequeNumber").val();
  const bankName = $("#bankName").val();
  const ifsc = $("#ifsc").val();
  const branchName = $("#branchName").val();

  if (!studentId || studentId.trim() === "" || parseInt(studentId) <= 0) {
    showAlert("Student selection is required.", "danger");
    $("#student-select-wrapper").addClass("is-invalid");
    $("#studentId-trigger").focus();
    return false;
  } else {
    $("#student-select-wrapper").removeClass("is-invalid");
  }

  if (!feeType || feeType.trim() === "") {
    showAlert("Fee Type is required.", "danger");
    $("#feeType").focus();
    return false;
  }

  if (!paidOnDate || paidOnDate.trim() === "") {
    showAlert("Paid On Date is required.", "danger");
    $("#paidOnDate").focus();
    return false;
  }

  const todayStr = new Date().toISOString().split('T')[0];
  if (paidOnDate > todayStr) {
    showAlert("Paid On Date cannot be a future date.", "danger");
    $("#paidOnDate").focus();
    return false;
  }

  if (!chequeNumber || chequeNumber.trim() === "") {
    showAlert("Cheque Number is required.", "danger");
    $("#chequeNumber").focus();
    return false;
  }

  if (!bankName || bankName.trim() === "") {
    showAlert("Bank Name is required.", "danger");
    $("#bankName").focus();
    return false;
  }

  if (!ifsc || ifsc.trim() === "") {
    showAlert("IFSC Code is required.", "danger");
    $("#ifsc").focus();
    return false;
  }

  if (!branchName || branchName.trim() === "") {
    showAlert("Branch Name is required.", "danger");
    $("#branchName").focus();
    return false;
  }

  // Validate Receipt File Extension if selected
  const receiptFile = $("#receipt")[0];
  if (receiptFile && receiptFile.files && receiptFile.files.length > 0) {
    if (!isValidFileExtension(receiptFile.files[0].name)) {
      showAlert("Invalid Receipt file format. Allowed formats: PDF, JPG, JPEG, PNG.", "danger");
      $("#receipt").focus();
      return false;
    }
  }

  return true;
}

/** Lock submit button during AJAX execution */
function lockSubmit() {
  $(".btn-submit")
    .attr("disabled", "")
    .find(".btn-submit-text")
    .text("Saving...");
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();
}

/** Unlock submit button on AJAX failure */
function unlockSubmit() {
  $(".btn-submit")
    .removeAttr("disabled")
    .find(".btn-submit-text")
    .text("Submit");
  $(".btn-submit-spinner").hide();
}

/** Helper for generating application-relative URLs */
function url(path) {
  const base = (window.__APP__ && typeof window.__APP__.baseUrl === 'string')
    ? window.__APP__.baseUrl
    : (window.location.pathname.split('/fee-payments')[0] || '');
  return base + path;
}

/** Extract structured error message from XHR response */
function extractErrorMessage(xhr) {
  if (xhr.status === 401 || xhr.status === 403) {
    return "Your session has expired or access was denied. Please log in again.";
  }
  try {
    const json = JSON.parse(xhr.responseText);
    if (json && json.message) return json.message;
  } catch (_) {}
  return xhr.responseText || "An unexpected error occurred. Please try again.";
}

// ── Add Fee Payment ───────────────────────────────────────────────────────────
function addPayment(event) {
  event.preventDefault();

  if (!validatePaymentForm()) {
    return false;
  }

  lockSubmit();
  submitting = true;

  const formData = new FormData($("#payment-form")[0]);

  $.ajax({
    method:      "POST",
    url:         url("/fee-payments/api/add"),
    contentType: false,
    processData: false,
    data:        formData,
    error: function (xhr) {
      submitting = false;
      unlockSubmit();
      const msg = extractErrorMessage(xhr);
      showAlert(msg, xhr.status === 409 ? "warning" : "danger");
    },
    success: function () {
      formDirty = false;
      $(".btn-submit-text").hide();
      $(".btn-submit-text-saved").show();
      $(".btn-submit-spinner").hide();

      setTimeout(function () {
        window.location.href = url("/fee-payments");
      }, 1000);
    },
  });

  return false;
}

// ── Edit Fee Payment ──────────────────────────────────────────────────────────
function editPayment(id, event) {
  event.preventDefault();

  if (!validatePaymentForm()) {
    return false;
  }

  lockSubmit();
  submitting = true;

  const formData = new FormData($("#payment-form")[0]);

  $.ajax({
    method:      "POST",
    url:         url(`/fee-payments/api/edit?id=${id}`),
    contentType: false,
    processData: false,
    data:        formData,
    error: function (xhr) {
      submitting = false;
      unlockSubmit();
      const msg = extractErrorMessage(xhr);
      showAlert(msg, xhr.status === 409 ? "warning" : "danger");
    },
    success: function () {
      formDirty = false;
      $(".btn-submit-text").hide();
      $(".btn-submit-text-saved").show();
      $(".btn-submit-spinner").hide();

      setTimeout(function () {
        window.location.href = url("/fee-payments");
      }, 1000);
    },
  });

  return false;
}

// ── Delete Fee Payment ────────────────────────────────────────────────────────
function showDeleteConfirmation(id) {
  $("#btn-yes").attr("data-id", id);
  $("#btn-yes-text").text("Yes, Delete").show();
  $("#btn-yes-spinner").hide();
  $("#btn-yes").removeAttr("disabled");
  $("#modal-delete").modal("show");
}

async function _delete() {
  const id = $("#btn-yes").attr("data-id");
  if (!id) return;

  $("#btn-yes-text").text("Deleting...");
  $("#btn-yes-spinner").show();
  $("#btn-yes").attr("disabled", "");

  try {
    const response = await fetch(url(`/fee-payments/api/delete?id=${id}`), {
      method: "DELETE",
    });

    if (response.ok) {
      $("#modal-delete").modal("hide");
      showFlashAlert("Fee payment deleted successfully.", "success");
      setTimeout(function () {
        window.location.reload();
      }, 1200);
    } else {
      let message = "An error occurred while deleting.";
      try {
        const json = await response.json();
        if (json && json.message) message = json.message;
      } catch (_) {}

      $("#modal-delete").modal("hide");
      showFlashAlert(message, "danger");

      $("#btn-yes-text").text("Yes, Delete").show();
      $("#btn-yes-spinner").hide();
      $("#btn-yes").removeAttr("disabled");
    }
  } catch (err) {
    $("#modal-delete").modal("hide");
    showFlashAlert("Network error. Please try again.", "danger");
    $("#btn-yes-text").text("Yes, Delete").show();
    $("#btn-yes-spinner").hide();
    $("#btn-yes").removeAttr("disabled");
  }
}
