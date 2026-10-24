/* ============================================================
   exams.js  –  PhD Portal | Admission Examination Management
   Handles: client validation, AJAX add/edit/delete, unsaved-
   changes warning, and Bootstrap alerts.
   ============================================================ */

"use strict";

let formDirty = false;
let submitting = false;

$(document).ready(function () {
  // Mark form dirty when any field changes
  $("#exam-form input, #exam-form select").on("input change", function () {
    formDirty = true;
  });

  // Warn before leaving if dirty and not submitting
  window.addEventListener("beforeunload", function (e) {
    if (formDirty && !submitting) {
      e.preventDefault();
      e.returnValue = "";
    }
  });

  // Activate Bootstrap tooltips on index page
  $('[data-toggle="tooltip"]').tooltip();
});

/**
 * Show a Bootstrap alert inside #form-alert.
 * @param {string} message  - The message to show.
 * @param {"danger"|"warning"|"success"} type
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
  $("html, body").animate({ scrollTop: $el.offset().top - 80 }, 300);
}

/**
 * Show a Bootstrap alert inside #flash-alert (index page).
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
  $("html, body").animate({ scrollTop: $el.offset().top - 80 }, 300);
}

/** Client-side form validation matching functional requirements */
function validateExamForm() {
  const batchId = $("#batchId").val();
  const writtenDate = $("#dateAndTimeOfWrittenExam").val();
  const presentationDate = $("#dateAndTimeOfPresentation").val();
  const maxWrittenMarks = $("#maximumMarksOfWrittenExam").val();
  const maxPresentationMarks = $("#maximumMarksOfPresentation").val();

  if (!batchId || batchId.trim() === "" || parseInt(batchId) <= 0) {
    showAlert("Batch is mandatory.", "danger");
    $("#batchId").focus();
    return false;
  }

  if (!writtenDate || writtenDate.trim() === "") {
    showAlert("Written Exam Date & Time cannot be empty.", "danger");
    $("#dateAndTimeOfWrittenExam").focus();
    return false;
  }

  if (!presentationDate || presentationDate.trim() === "") {
    showAlert("Presentation Date & Time cannot be empty.", "danger");
    $("#dateAndTimeOfPresentation").focus();
    return false;
  }

  if (!maxWrittenMarks || maxWrittenMarks.trim() === "") {
    showAlert("Maximum Marks (Written Exam) cannot be empty.", "danger");
    $("#maximumMarksOfWrittenExam").focus();
    return false;
  }

  if (!maxPresentationMarks || maxPresentationMarks.trim() === "") {
    showAlert("Maximum Marks (Presentation) cannot be empty.", "danger");
    $("#maximumMarksOfPresentation").focus();
    return false;
  }

  return true;
}

/** Lock submit button during processing */
function lockSubmit() {
  $(".btn-submit")
    .attr("disabled", "")
    .find(".btn-submit-text")
    .text("Saving...");
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();
}

/** Unlock submit button on failure */
function unlockSubmit() {
  $(".btn-submit")
    .removeAttr("disabled")
    .find(".btn-submit-text")
    .text("Submit");
  $(".btn-submit-spinner").hide();
}

/** Utility helper for absolute URLs in application context */
function url(path) {
  const baseDir = window.location.pathname.split('/admission-exams')[0];
  return baseDir + path;
}

/** Try to extract structured JSON error messages from standard API responses */
function extractErrorMessage(xhr) {
  try {
    const json = JSON.parse(xhr.responseText);
    if (json && json.message) return json.message;
  } catch (_) {}
  return xhr.responseText || "An unexpected error occurred. Please try again.";
}

// ── Add Exam ──────────────────────────────────────────────────────────────────
function addExam(event) {
  event.preventDefault();

  if (!validateExamForm()) {
    return false;
  }

  lockSubmit();
  submitting = true;

  const formData = new FormData($("#exam-form")[0]);

  $.ajax({
    method:      "POST",
    url:         url("/admission-exams/api/add"),
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
        window.location.href = url("/admission-exams");
      }, 1000);
    },
  });

  return false;
}

// ── Edit Exam ─────────────────────────────────────────────────────────────────
function editExam(id, event) {
  event.preventDefault();

  if (!validateExamForm()) {
    return false;
  }

  lockSubmit();
  submitting = true;

  const formData = new FormData($("#exam-form")[0]);

  $.ajax({
    method:      "POST",
    url:         url(`/admission-exams/api/edit?id=${id}`),
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
        window.location.href = url("/admission-exams");
      }, 1000);
    },
  });

  return false;
}

// ── Delete Exam ───────────────────────────────────────────────────────────────
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
    const response = await fetch(url(`/admission-exams/api/delete?id=${id}`), {
      method: "DELETE",
    });

    if (response.ok) {
      $("#modal-delete").modal("hide");
      showFlashAlert("Admission exam deleted successfully.", "success");
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
