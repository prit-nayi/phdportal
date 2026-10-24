/* ============================================================
   re-registrations.js  –  PhD Portal | Re-Registration Management
   Handles: client validation, add, edit, delete, unsaved-changes
   warning, double-submit prevention, Bootstrap alerts.
   ============================================================ */

"use strict";

// ── Unsaved changes tracking ───────────────────────────────────────────────────
let formDirty  = false;
let submitting = false;

$(document).ready(function () {
  // Mark form dirty when any field changes
  $("#rereg-form input, #rereg-form select, #rereg-form textarea")
    .on("input change", function () {
      formDirty = true;
    });

  // Warn before leaving if dirty and not submitting
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

// ── Submit helpers ─────────────────────────────────────────────────────────────

function lockSubmit() {
  $(".btn-submit")
    .attr("disabled", "")
    .find(".btn-submit-text")
    .text("Saving...");
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();
}

function unlockSubmit() {
  $(".btn-submit")
    .removeAttr("disabled")
    .find(".btn-submit-text")
    .text("Submit");
  $(".btn-submit-spinner").hide();
  $(".btn-submit-text").show();
}

function extractErrorMessage(xhr) {
  try {
    const json = JSON.parse(xhr.responseText);
    if (json && json.message) return json.message;
  } catch (_) {}
  return xhr.responseText || "An unexpected error occurred. Please try again.";
}

// ── Validation ─────────────────────────────────────────────────────────────────

function validateAddForm() {
  const studentId = $("#studentId").val();
  if (!studentId || studentId === "") {
    showAlert("Please select a student.", "danger");
    $("#studentId").focus();
    return false;
  }
  return true;
}

// ── Add ────────────────────────────────────────────────────────────────────────

function addReRegistration(event) {
  event.preventDefault();

  if (!validateAddForm()) return false;

  lockSubmit();
  submitting = true;

  const formData = new FormData();
  formData.append("studentId", $("#studentId").val());
  formData.append("reason", $("#reason").val().trim());

  $.ajax({
    method:      "POST",
    url:         url("/re-registrations/api/add"),
    contentType: false,
    processData: false,
    data:        formData,
    error: function (xhr) {
      submitting = false;
      unlockSubmit();
      showAlert(extractErrorMessage(xhr), "danger");
    },
    success: function () {
      formDirty = false;
      $(".btn-submit-text").hide();
      $(".btn-submit-text-saved").show();
      $(".btn-submit-spinner").hide();
      setTimeout(function () {
        window.location.href = url("/re-registrations");
      }, 1000);
    },
  });

  return false;
}

// ── Edit ───────────────────────────────────────────────────────────────────────

function editReRegistration(id, event) {
  event.preventDefault();

  lockSubmit();
  submitting = true;

  const formData = new FormData();
  formData.append("reason", $("#reason").val().trim());

  $.ajax({
    method:      "POST",
    url:         url(`/re-registrations/api/edit?id=${id}`),
    contentType: false,
    processData: false,
    data:        formData,
    error: function (xhr) {
      submitting = false;
      unlockSubmit();
      showAlert(extractErrorMessage(xhr), "danger");
    },
    success: function () {
      formDirty = false;
      $(".btn-submit-text").hide();
      $(".btn-submit-text-saved").show();
      $(".btn-submit-spinner").hide();
      setTimeout(function () {
        window.location.href = url("/re-registrations");
      }, 1000);
    },
  });

  return false;
}

// ── Delete ─────────────────────────────────────────────────────────────────────

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
    const response = await fetch(url(`/re-registrations/api/delete?id=${id}`), {
      method: "DELETE",
    });

    if (response.ok) {
      $("#modal-delete").modal("hide");
      showFlashAlert("Re-registration deleted successfully.", "success");
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
  } catch (networkError) {
    $("#modal-delete").modal("hide");
    showFlashAlert("Network error. Please check your connection.", "danger");
    $("#btn-yes-text").text("Yes, Delete").show();
    $("#btn-yes-spinner").hide();
    $("#btn-yes").removeAttr("disabled");
  }
}
