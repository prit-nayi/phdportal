/* ============================================================
   guide-allocation-presentations.js – PhD Portal
   Handles: client validation, add, edit, delete, unsaved-
   changes tracking, and Bootstrap alerts for presentations.
   ============================================================ */

"use strict";

let formDirty = false;
let submitting = false;

$(document).ready(function () {
  // Mark form dirty when any input or select changes
  $("#presentation-form input, #presentation-form select").on("input change", function () {
    formDirty = true;
  });

  // Warn before leaving page if dirty and not submitting
  window.addEventListener("beforeunload", function (e) {
    if (formDirty && !submitting) {
      e.preventDefault();
      e.returnValue = "";
    }
  });

  // Activate Bootstrap tooltips
  $('[data-toggle="tooltip"]').tooltip();
});

// ── Alert helpers ──────────────────────────────────────────────────────────────

/**
 * Show a Bootstrap alert inside #form-alert.
 * @param {string} message 
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
 * Show a Bootstrap alert inside #flash-alert (list page).
 * @param {string} message 
 * @param {"danger"|"warning"|"success"} type 
 */
function showFlashAlert(message, type) {
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
  const $el = $("#flash-alert");
  $el.html(html).show();
  $("html, body").animate({ scrollTop: 0 }, 300);
}

/** Validate client input for Presentation form */
function validatePresentationForm() {
  const batchId = $("#batchId").val();
  const name = $("#name").val().trim();
  const scheduledOn = $("#scheduledOn").val();

  if (!batchId || batchId === "") {
    showAlert("Please select a Batch.", "danger");
    $("#batchId").focus();
    return false;
  }

  if (name === "") {
    showAlert("Presentation Name is required.", "danger");
    $("#name").focus();
    return false;
  }

  if (name.length > 255) {
    showAlert("Presentation Name cannot exceed 255 characters.", "danger");
    $("#name").focus();
    return false;
  }

  if (!scheduledOn || scheduledOn === "") {
    showAlert("Scheduled Date is required.", "danger");
    $("#scheduledOn").focus();
    return false;
  }

  return true;
}

/** Lock submit button and display spinner */
function lockSubmit() {
  $(".btn-submit")
    .attr("disabled", "")
    .find(".btn-submit-text")
    .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();
}

/** Restore submit button state */
function unlockSubmit() {
  $(".btn-submit").removeAttr("disabled");
  $(".btn-submit-spinner").hide();
  $(".btn-submit-text").show();
}

/** Extract human-readable error message from response */
function extractErrorMessage(xhr) {
  try {
    const json = JSON.parse(xhr.responseText);
    if (json && json.message) return json.message;
  } catch (_) { /* not JSON */ }
  return xhr.responseText || "An unexpected error occurred. Please try again.";
}

// ── Add Presentation ───────────────────────────────────────────────────────────

function add(event) {
  event.preventDefault();

  if (!validatePresentationForm()) {
    return false;
  }

  lockSubmit();
  submitting = true;

  const formData = new FormData();
  formData.append("batchId", $("#batchId").val());
  formData.append("name", $("#name").val().trim());
  formData.append("scheduledOn", $("#scheduledOn").val());

  $.ajax({
    method: "POST",
    url: url("/guide-allocation-presentations/api/add"),
    contentType: false,
    processData: false,
    data: formData,
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
        window.location.href = url("/guide-allocation-presentations");
      }, 800);
    },
  });

  return false;
}

// ── Edit Presentation ──────────────────────────────────────────────────────────

function edit(id, event) {
  event.preventDefault();

  if (!validatePresentationForm()) {
    return false;
  }

  lockSubmit();
  submitting = true;

  const formData = new FormData();
  formData.append("batchId", $("#batchId").val());
  formData.append("name", $("#name").val().trim());
  formData.append("scheduledOn", $("#scheduledOn").val());

  $.ajax({
    method: "POST",
    url: url(`/guide-allocation-presentations/api/edit?id=${id}`),
    contentType: false,
    processData: false,
    data: formData,
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
        window.location.href = url("/guide-allocation-presentations");
      }, 800);
    },
  });

  return false;
}

// ── Delete Presentation ────────────────────────────────────────────────────────

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
    const response = await fetch(url(`/guide-allocation-presentations/api/delete?id=${id}`), {
      method: "DELETE",
    });

    if (response.ok) {
      $("#modal-delete").modal("hide");
      showFlashAlert("Presentation Schedule deleted successfully.", "success");
      setTimeout(function () {
        window.location.reload();
      }, 800);
    } else {
      let message = "An error occurred while deleting.";
      try {
        const json = await response.json();
        if (json && json.message) message = json.message;
      } catch (_) { /* not JSON */ }

      $("#modal-delete").modal("hide");
      showFlashAlert(message, response.status === 409 ? "warning" : "danger");

      $("#btn-yes-text").text("Yes, Delete").show();
      $("#btn-yes-spinner").hide();
      $("#btn-yes").removeAttr("disabled");
    }
  } catch (networkError) {
    $("#modal-delete").modal("hide");
    showFlashAlert("Network error. Please check your connection.", "danger");
    $("#btn-yes-text").text("Yes, Delete").show();
    $("#btn-yes-spinner").hide();
    $("#btn-yes").removeAttr("disabled");
  }
}
