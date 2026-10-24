/* ============================================================
   presentation-ppt.js – PhD Portal | Presentation PPT Management
   Handles: Rendering PPT tab table, client-side validation (.ppt, .pptx <= 20MB),
   AJAX batch uploading with progress, single-file replacement, and modal confirmation removal.
   ============================================================ */

"use strict";

let pendingRemoveStudentId = null;

/**
 * Render Presentation PPT student table
 */
function renderPptTable() {
  const $pptTbody = $('#ppt-table-body').empty();

  if (!studentsState || studentsState.length === 0) {
    return;
  }

  studentsState.forEach((stu, index) => {
    const srNo = index + 1;
    const hasPpt = stu.presentationPptFile && stu.presentationPptFile !== '';
    const fileUrl = stu.presentationPptUrl || (hasPpt ? url('/' + stu.presentationPptFile.replace(/^\//, '')) : '#');

    let existingFileHtml = '';
    if (hasPpt) {
      existingFileHtml = `
        <div class="btn-group btn-group-sm" role="group" aria-label="PPT Actions">
          <a href="${escapeHtml(fileUrl)}" target="_blank" class="btn btn-outline-info btn-xs" title="View File">
            <i class="fas fa-eye mr-1"></i>View File
          </a>
          <button type="button" class="btn btn-outline-primary btn-xs" onclick="triggerReplaceFile(${stu.studentId});" title="Replace">
            <i class="fas fa-sync-alt mr-1"></i>Replace
          </button>
          <button type="button" class="btn btn-outline-danger btn-xs" onclick="promptRemovePpt(${stu.studentId});" title="Remove">
            <i class="fas fa-trash-alt mr-1"></i>Remove
          </button>
        </div>
      `;
    } else {
      existingFileHtml = `<span class="badge badge-light text-muted border font-weight-normal py-1 px-2"><i class="fas fa-info-circle mr-1"></i>No PPT Uploaded</span>`;
    }

    const row = `
      <tr id="ppt-row-${stu.studentId}">
        <td class="align-middle font-weight-bold">${srNo}</td>
        <td class="align-middle text-muted">${escapeHtml(stu.applicationNo)}</td>
        <td class="align-middle text-muted">${escapeHtml(stu.enrollmentNo)}</td>
        <td class="align-middle font-weight-bold text-dark">${escapeHtml(stu.studentName)}</td>
        <td class="align-middle">
          <div class="custom-file">
            <input type="file" class="custom-file-input ppt-file-input"
                   id="ppt-input-${stu.studentId}" data-student-id="${stu.studentId}"
                   onchange="onPptFileSelect(this, ${stu.studentId});">
            <label class="custom-file-label text-truncate text-muted" for="ppt-input-${stu.studentId}">Choose File</label>
            <div class="invalid-feedback err-ppt-${stu.studentId}"></div>
          </div>
        </td>
        <td class="align-middle existing-ppt-cell-${stu.studentId}">
          ${existingFileHtml}
        </td>
      </tr>
    `;
    $pptTbody.append(row);
  });

  // Re-initialize Bootstrap custom file inputs
  if (typeof bsCustomFileInput !== 'undefined') {
    bsCustomFileInput.init();
  }
}

/**
 * Handle PPT File selection and client-side validation
 */
function onPptFileSelect(inputEl, studentId) {
  const $input = $(inputEl);
  const $err = $(`.err-ppt-${studentId}`);
  const file = inputEl.files && inputEl.files[0];

  $input.removeClass('is-invalid');
  $err.text('').hide();

  if (!file) {
    $input.next('.custom-file-label').html('Choose File').addClass('text-muted');
    return;
  }

  // Update label
  $input.next('.custom-file-label').text(file.name).removeClass('text-muted');

  // Validate max size (20MB)
  const maxBytes = 20 * 1024 * 1024;
  if (file.size > maxBytes) {
    $input.addClass('is-invalid');
    $err.text('Maximum file size exceeded (Max 20MB).').show();
    inputEl.value = '';
    $input.next('.custom-file-label').html('Choose File').addClass('text-muted');
    return;
  }

  formDirty = true;
}

/**
 * Save all selected PPT files for active batch
 */
function savePresentationPptFiles() {
  const examId = $('#examId').val();
  if (!examId || parseInt(examId) <= 0) {
    showAlert("Please select an Admission Exam.", "danger");
    return;
  }

  const formData = new FormData();
  formData.append('examId', examId);

  let selectedCount = 0;
  let hasClientError = false;

  $('.ppt-file-input').each(function () {
    const studentId = $(this).attr('data-student-id');
    const file = this.files && this.files[0];

    if ($(this).hasClass('is-invalid')) {
      hasClientError = true;
    }

    if (file) {
      formData.append(`ppt_file_${studentId}`, file);
      selectedCount++;
    }
  });

  if (hasClientError) {
    showAlert("Please fix file validation errors highlighted in red before uploading.", "danger");
    return;
  }

  if (selectedCount === 0) {
    showAlert("No new files selected to save. Please select at least one file.", "warning");
    return;
  }

  submitting = true;
  lockSubmit();

  $.ajax({
    method: "POST",
    url: url("/admission-exam-results/api/upload-presentation-ppt"),
    data: formData,
    contentType: false,
    processData: false,
    xhr: function () {
      const xhr = new window.XMLHttpRequest();
      xhr.upload.addEventListener("progress", function (evt) {
        if (evt.lengthComputable) {
          const percentComplete = Math.round((evt.loaded / evt.total) * 100);
          $('#save-btn-label').text(`Uploading... ${percentComplete}%`);
        }
      }, false);
      return xhr;
    },
    success: function (res) {
      formDirty = false;
      submitting = false;
      unlockSubmit();
      $('#save-btn-label').text('Save PPT Files');

      if (res.data && res.data.saved) {
        res.data.saved.forEach(savedItem => {
          const stu = studentsState.find(s => s.studentId === savedItem.studentId);
          if (stu) {
            stu.presentationPptFile = savedItem.relativePath;
            stu.presentationPptUrl = savedItem.fileUrl;
          }
        });
      }

      showAlert(res.message || "Presentation PPT files uploaded successfully.", "success");
      renderPptTable();
    },
    error: function (xhr) {
      submitting = false;
      unlockSubmit();
      $('#save-btn-label').text('Save PPT Files');
      showAlert(extractErrorMessage(xhr), "danger");
    }
  });
}

/**
 * Trigger single file replacement chooser
 */
function triggerReplaceFile(studentId) {
  const $input = $(`#ppt-input-${studentId}`);
  if ($input.length) {
    $input.off('change.replace').on('change.replace', function () {
      const file = this.files && this.files[0];
      if (file && !$(this).hasClass('is-invalid')) {
        uploadSingleReplacePpt(studentId, file);
      }
    });
    $input.trigger('click');
  }
}

/**
 * Upload replaced single PPT file via AJAX
 */
function uploadSingleReplacePpt(studentId, file) {
  const examId = $('#examId').val();
  const formData = new FormData();
  formData.append('examId', examId);
  formData.append('studentId', studentId);
  formData.append('ppt_file', file);

  showAlert(`Replacing presentation PPT file for student...`, "info");

  $.ajax({
    method: "POST",
    url: url("/admission-exam-results/api/replace-presentation-ppt"),
    data: formData,
    contentType: false,
    processData: false,
    success: function (res) {
      if (res.data) {
        const stu = studentsState.find(s => s.studentId === studentId);
        if (stu) {
          stu.presentationPptFile = res.data.relativePath;
          stu.presentationPptUrl = res.data.fileUrl;
        }
      }
      showAlert(res.message || "Presentation PPT file replaced successfully.", "success");
      renderPptRow(studentId);
    },
    error: function (xhr) {
      showAlert(extractErrorMessage(xhr), "danger");
    }
  });
}

/**
 * Open confirmation modal to remove PPT
 */
function promptRemovePpt(studentId) {
  pendingRemoveStudentId = studentId;
  $('#removePptModal').modal('show');
}

/**
 * Delete presentation PPT file after modal confirmation
 */
function confirmDeletePpt() {
  if (!pendingRemoveStudentId) return;

  const examId = $('#examId').val();
  const studentId = pendingRemoveStudentId;

  $('#confirmRemovePptBtn').attr('disabled', '');
  $('#remove-ppt-spinner').show();

  $.ajax({
    method: "POST",
    url: url("/admission-exam-results/api/delete-presentation-ppt"),
    contentType: "application/json",
    data: JSON.stringify({ examId: parseInt(examId), studentId: parseInt(studentId) }),
    dataType: "json",
    success: function (res) {
      $('#removePptModal').modal('hide');
      $('#confirmRemovePptBtn').removeAttr('disabled');
      $('#remove-ppt-spinner').hide();

      const stu = studentsState.find(s => s.studentId === studentId);
      if (stu) {
        stu.presentationPptFile = null;
        stu.presentationPptUrl = null;
      }

      showAlert(res.message || "Presentation PPT file removed successfully.", "success");
      renderPptRow(studentId);
      pendingRemoveStudentId = null;
    },
    error: function (xhr) {
      $('#removePptModal').modal('hide');
      $('#confirmRemovePptBtn').removeAttr('disabled');
      $('#remove-ppt-spinner').hide();
      showAlert(extractErrorMessage(xhr), "danger");
      pendingRemoveStudentId = null;
    }
  });
}

/**
 * Refresh a single student row in the PPT table
 */
function renderPptRow(studentId) {
  const stu = studentsState.find(s => s.studentId === studentId);
  if (!stu) return;

  const hasPpt = stu.presentationPptFile && stu.presentationPptFile !== '';
  const fileUrl = stu.presentationPptUrl || (hasPpt ? url('/' + stu.presentationPptFile.replace(/^\//, '')) : '#');

  let existingFileHtml = '';
  if (hasPpt) {
    existingFileHtml = `
      <div class="btn-group btn-group-sm" role="group" aria-label="PPT Actions">
        <a href="${escapeHtml(fileUrl)}" target="_blank" class="btn btn-outline-info btn-xs" title="View File">
          <i class="fas fa-eye mr-1"></i>View File
        </a>
        <button type="button" class="btn btn-outline-primary btn-xs" onclick="triggerReplaceFile(${stu.studentId});" title="Replace">
          <i class="fas fa-sync-alt mr-1"></i>Replace
        </button>
        <button type="button" class="btn btn-outline-danger btn-xs" onclick="promptRemovePpt(${stu.studentId});" title="Remove">
          <i class="fas fa-trash-alt mr-1"></i>Remove
        </button>
      </div>
    `;
  } else {
    existingFileHtml = `<span class="badge badge-light text-muted border font-weight-normal py-1 px-2"><i class="fas fa-info-circle mr-1"></i>No PPT Uploaded</span>`;
  }

  $(`.existing-ppt-cell-${studentId}`).html(existingFileHtml);
  $(`#ppt-input-${studentId}`).val('').removeClass('is-invalid');
  $(`#ppt-input-${studentId}`).next('.custom-file-label').html('Choose File').addClass('text-muted');
  $(`.err-ppt-${studentId}`).text('').hide();
}
