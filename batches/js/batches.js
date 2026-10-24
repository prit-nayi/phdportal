/* ============================================================
   batches.js  –  PhD Portal | Batch Management
   Handles: client validation, add, edit, delete, unsaved-
   changes warning, double-submit prevention, Bootstrap alerts.
   ============================================================ */

"use strict";


// ── Unsaved changes tracking ───────────────────────────────────────────────────
let formDirty = false;
let submitting = false;

$(document).ready(function () {
  // Mark form dirty when any field changes
  $("#batch-form input").on("input change", function () {
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

// ── Alert helpers ──────────────────────────────────────────────────────────────

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
  // Scroll to alert
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



/** Check that none of the three fields are empty. Returns true if all filled. */
function validateRequired() {
  const fields = [
    { id: "name",           label: "Batch Name" },
    { id: "enrollmentYear", label: "Enrollment Year" },
    { id: "feeAmount",      label: "Fee Amount" },
  ];

  for (const field of fields) {
    if ($("#" + field.id).val().trim() === "") {
      showAlert(field.label + " is required.", "danger");
      $("#" + field.id).focus();
      return false;
    }
  }
  return true;
}

/** Lock the submit button and show spinner */
function lockSubmit() {
  $(".btn-submit")
    .attr("disabled", "")
    .find(".btn-submit-text")
    .text("Saving...");
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();
}

/** Restore submit button after failure */
function unlockSubmit() {
  $(".btn-submit")
    .removeAttr("disabled")
    .find(".btn-submit-text")
    .text("Submit");
  $(".btn-submit-spinner").hide();
  $(".btn-submit-text").show();
}

/** Extract a human-readable message from an AJAX error response */
function extractErrorMessage(xhr) {
  try {
    const json = JSON.parse(xhr.responseText);
    if (json && json.message) return json.message;
  } catch (_) { /* not JSON */ }
  return xhr.responseText || "An unexpected error occurred. Please try again.";
}

// ── Add ────────────────────────────────────────────────────────────────────────

function add(event) {
  event.preventDefault();

  if (!validateRequired()) {
    return false;
  }

  lockSubmit();
  submitting = true;

  const formData = new FormData();
  formData.append("name",           $("#name").val().trim());
  formData.append("enrollmentYear", $("#enrollmentYear").val().trim());
  formData.append("feeAmount",      $("#feeAmount").val().trim());

  $.ajax({
    method:      "POST",
    url:         url("/batches/api/add"),
    contentType: false,
    processData: false,
    data:        formData,
    error: function (xhr) {
      submitting = false;
      unlockSubmit();
      const msg = extractErrorMessage(xhr);
      // 409 = duplicate → show as warning
      showAlert(msg, xhr.status === 409 ? "warning" : "danger");
    },
    success: function () {
      formDirty = false;
      $(".btn-submit-text").hide();
      $(".btn-submit-text-saved").show();
      $(".btn-submit-spinner").hide();

      setTimeout(function () {
        window.location.href = url("/batches");
      }, 1000);
    },
  });

  return false;
}

// ── Edit ───────────────────────────────────────────────────────────────────────

function edit(id, event) {
  event.preventDefault();

  if (!validateRequired()) {
    return false;
  }

  lockSubmit();
  submitting = true;

  const formData = new FormData();
  formData.append("name",           $("#name").val().trim());
  formData.append("enrollmentYear", $("#enrollmentYear").val().trim());
  formData.append("feeAmount",      $("#feeAmount").val().trim());

  $.ajax({
    method:      "POST",
    url:         url(`/batches/api/edit?id=${id}`),
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
        window.location.href = url("/batches");
      }, 1000);
    },
  });

  return false;
}

// ── Delete ─────────────────────────────────────────────────────────────────────

function showDeleteConfirmation(id) {
  $("#btn-yes").attr("data-id", id);
  // Reset modal button state in case a previous delete failed
  $("#btn-yes-text").text("Yes, Delete").show();
  $("#btn-yes-spinner").hide();
  $("#btn-yes").removeAttr("disabled");
  $("#modal-delete").modal("show");
}

async function _delete() {
  const id = $("#btn-yes").attr("data-id");
  if (!id) return;

  // Lock delete button
  $("#btn-yes-text").text("Deleting...");
  $("#btn-yes-spinner").show();
  $("#btn-yes").attr("disabled", "");

  try {
    const response = await fetch(url(`/batches/api/delete?id=${id}`), {
      method: "DELETE",
    });

    if (response.ok) {
      $("#modal-delete").modal("hide");
      showFlashAlert("Batch deleted successfully.", "success");
      // Remove the row from DOM without full page reload
      setTimeout(function () {
        window.location.reload();
      }, 1200);
    } else {
      let message = "An error occurred while deleting.";
      try {
        const json = await response.json();
        if (json && json.message) message = json.message;
      } catch (_) { /* not JSON */ }

      $("#modal-delete").modal("hide");
      showFlashAlert(
        message,
        response.status === 409 ? "warning" : "danger"
      );

      // Restore delete button
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
