/* ============================================================
   guides.js – PhD Portal | PhD Guide Management
   Handles: client validation, add, edit, status toggle,
   unsaved-changes tracking, and Bootstrap alerts.
   ============================================================ */

"use strict";

let formDirty = false;
let submitting = false;

$(document).ready(function () {
  // Mark form dirty when any input changes
  $("#guide-form input, #guide-form select").on("input change", function () {
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

/** Validate client input for Guide form */
function validateGuideForm() {
  const name = $("#name").val().trim();
  const designation = $("#designation").val().trim();
  const maxScholars = $("#maximumNumberOfScholars").val().trim();

  if (name === "") {
    showAlert("Guide Name is required.", "danger");
    $("#name").focus();
    return false;
  }
  if (name.length > 255) {
    showAlert("Guide Name cannot exceed 255 characters.", "danger");
    $("#name").focus();
    return false;
  }

  if (designation === "") {
    showAlert("Designation is required.", "danger");
    $("#designation").focus();
    return false;
  }
  if (designation.length > 255) {
    showAlert("Designation cannot exceed 255 characters.", "danger");
    $("#designation").focus();
    return false;
  }

  if (maxScholars === "" || isNaN(maxScholars)) {
    showAlert("Maximum Number of Scholars must be a valid integer.", "danger");
    $("#maximumNumberOfScholars").focus();
    return false;
  }

  const maxCap = parseInt(maxScholars, 10);
  if (maxCap < 0) {
    showAlert("Maximum Number of Scholars cannot be negative.", "danger");
    $("#maximumNumberOfScholars").focus();
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

// ── Add Guide ──────────────────────────────────────────────────────────────────

function add(event) {
  event.preventDefault();

  if (!validateGuideForm()) {
    return false;
  }

  lockSubmit();
  submitting = true;

  const formData = new FormData();
  formData.append("name", $("#name").val().trim());
  formData.append("designation", $("#designation").val().trim());
  formData.append("maximumNumberOfScholars", $("#maximumNumberOfScholars").val().trim());
  formData.append("isActive", $("#isActive").val());

  $.ajax({
    method: "POST",
    url: url("/guides/api/add"),
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
        window.location.href = url("/guides");
      }, 800);
    },
  });

  return false;
}

// ── Edit Guide ─────────────────────────────────────────────────────────────────

function edit(id, event) {
  event.preventDefault();

  if (!validateGuideForm()) {
    return false;
  }

  lockSubmit();
  submitting = true;

  const formData = new FormData();
  formData.append("name", $("#name").val().trim());
  formData.append("designation", $("#designation").val().trim());
  formData.append("maximumNumberOfScholars", $("#maximumNumberOfScholars").val().trim());
  formData.append("isActive", $("#isActive").val());

  $.ajax({
    method: "POST",
    url: url(`/guides/api/edit?id=${id}`),
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
        window.location.href = url("/guides");
      }, 800);
    },
  });

  return false;
}

// ── Change Active / Inactive Status ───────────────────────────────────────────

function changeStatus(event, button, id) {
  event.preventDefault();

  const $btn = $(button);
  const $text = $btn.find(".btn-submit-text");
  const $spinner = $btn.find(".btn-submit-spinner");

  $btn.attr("disabled", "");
  $text.hide();
  $spinner.show();

  $.ajax({
    method: "GET",
    url: url(`/guides/api/change-status?id=${id}`),
    error: function (xhr) {
      $btn.removeAttr("disabled");
      $text.show();
      $spinner.hide();
      const msg = extractErrorMessage(xhr);
      showFlashAlert(msg, "danger");
    },
    success: function () {
      showFlashAlert("Guide status updated successfully.", "success");
      setTimeout(function () {
        window.location.reload();
      }, 600);
    },
  });

  return false;
}
