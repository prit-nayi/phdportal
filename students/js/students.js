/* ==========================================================
   students.js  –  PhD Portal | Student Management
   Handles: single add, edit, delete, inline per-field validation,
   CSV bulk upload, double-submit prevention, Bootstrap alerts.
   ========================================================== */

"use strict";

let formDirty  = false;
let submitting = false;

$(document).ready(function () {
  $("#student-form input, #student-form select, #student-form textarea")
    .on("input change", function () {
      formDirty = true;
      // Clear individual field error on change
      clearFieldError(this);
    });

  window.addEventListener("beforeunload", function (e) {
    if (formDirty && !submitting) { e.preventDefault(); e.returnValue = ""; }
  });

  $('[data-toggle="tooltip"]').tooltip();

  // Update custom file inputs (e.g. Document Uploads) with selected file name
  $(".custom-file-input").on("change", function () {
    let fileName = $(this).val().split("\\").pop();
    let $label = $(this).siblings(".custom-file-label");
    if (fileName) {
      $label.addClass("selected").html(fileName);
    } else {
      $label.removeClass("selected").html("Choose file" + ($label.text().includes("replace") ? " to replace..." : ""));
    }
  });

  // CSV drag-and-drop
  const dropZone = document.getElementById("csv-drop-zone");
  if (dropZone) {
    dropZone.addEventListener("dragover", function (e) {
      e.preventDefault();
      dropZone.classList.add("drag-over");
    });
    dropZone.addEventListener("dragleave", function () {
      dropZone.classList.remove("drag-over");
    });
    dropZone.addEventListener("drop", function (e) {
      e.preventDefault();
      dropZone.classList.remove("drag-over");
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        document.getElementById("csvFile").files = files;
        onCsvFileSelected(document.getElementById("csvFile"));
      }
    });
  }
});

// ── Mode switcher ─────────────────────────────────────────────────────────────
function switchMode(mode) {
  const isSingle = (mode === "single");
  document.getElementById("panel-single").style.display = isSingle ? "" : "none";
  document.getElementById("panel-bulk").style.display   = isSingle ? "none" : "";
  document.getElementById("btn-mode-single").classList.toggle("active", isSingle);
  document.getElementById("btn-mode-bulk").classList.toggle("active", !isSingle);
  document.getElementById("form-alert").style.display = "none";
}

// ── Alert helpers ─────────────────────────────────────────────────────────────
function showAlert(message, type) {
  const icons = {
    danger:  "fas fa-times-circle",
    warning: "fas fa-exclamation-triangle",
    success: "fas fa-check-circle",
  };
  const icon = icons[type] || "fas fa-info-circle";
  const html = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
    <i class="${icon} mr-2"></i>${message}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>`;
  const $el = $("#form-alert");
  $el.html(html).show();
  $("html, body").animate({ scrollTop: $el.offset().top - 80 }, 300);
}

function showFlashAlert(message, type) {
  const icons = {
    danger:  "fas fa-times-circle",
    warning: "fas fa-exclamation-triangle",
    success: "fas fa-check-circle",
  };
  const icon = icons[type] || "fas fa-info-circle";
  const html = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
    <i class="${icon} mr-2"></i>${message}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>`;
  const $el = $("#flash-alert");
  $el.html(html).show();
  $("html, body").animate({ scrollTop: 0 }, 300);
}

// ── Per-field inline validation helpers ───────────────────────────────────────
function setFieldError(el, message) {
  const $el = $(el);
  $el.addClass("is-invalid");
  // Use sibling .invalid-feedback if present, else create one
  let $fb = $el.nextAll(".invalid-feedback").first();
  if ($fb.length === 0) {
    $fb = $('<div class="invalid-feedback"></div>');
    $el.after($fb);
  }
  $fb.text(message).show();
}

function clearFieldError(el) {
  const $el = $(el);
  $el.removeClass("is-invalid");
  $el.nextAll(".invalid-feedback").first().text("").hide();
}

function clearAllValidationState() {
  $("#student-form .is-invalid").removeClass("is-invalid");
  $("#student-form .invalid-feedback").text("").hide();
}

// ── Full form validation (inline, returns true/false) ─────────────────────────
function validateForm() {
  clearAllValidationState();
  let firstInvalid = null;

  const fields = $("#student-form [required]");
  fields.each(function () {
    const val = $(this).val();
    if (!val || val.trim() === "") {
      const label = $("label[for='" + this.id + "']").first().text().replace("*", "").trim()
                    || this.name.replace(/_/g, " ").replace(/([a-z])([A-Z])/g, "$1 $2");
      // Use the field's own .invalid-feedback text first, else fallback
      const $fb = $(this).nextAll(".invalid-feedback").first();
      if ($fb.length && $fb.text().trim() === "") {
        $fb.text(label + " is required.");
      }
      $(this).addClass("is-invalid");
      if (!firstInvalid) firstInvalid = this;
    }
  });

  if (firstInvalid) {
    // Scroll to first invalid field
    $("html, body").animate({ scrollTop: $(firstInvalid).offset().top - 120 }, 350, function () {
      $(firstInvalid).focus();
    });
    showAlert("Please fill in all required fields highlighted below.", "danger");
    return false;
  }
  return true;
}

// ── Submit helpers ────────────────────────────────────────────────────────────
function lockSubmit() {
  const $btn = $(".btn-submit");
  $btn.attr("disabled", "");
  $btn.find(".btn-submit-text").text("Saving...");
  $btn.find(".btn-submit-text-saved").hide();
  $btn.find(".btn-submit-spinner").show();
}

function unlockSubmit() {
  const $btn = $(".btn-submit");
  $btn.removeAttr("disabled");
  $btn.find(".btn-submit-text").text("Save Student");
  $btn.find(".btn-submit-spinner").hide();
  $btn.find(".btn-submit-text").show();
}

function extractErrorMessage(xhr) {
  try {
    const json = JSON.parse(xhr.responseText);
    if (json && json.message) return json.message;
  } catch (_) {}
  return xhr.responseText || "An unexpected error occurred. Please try again.";
}

// ── Add ───────────────────────────────────────────────────────────────────────
function addStudent(event) {
  event.preventDefault();

  if (!validateForm()) return false;

  lockSubmit();
  submitting = true;

  const formData = new FormData(document.getElementById("student-form"));

  $.ajax({
    method:      "POST",
    url:         url("/students/api/add"),
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
      setTimeout(function () { window.location.href = url("/students"); }, 1000);
    },
  });

  return false;
}

// ── Edit ──────────────────────────────────────────────────────────────────────
function editStudent(id, event) {
  event.preventDefault();

  if (!validateForm()) return false;

  lockSubmit();
  submitting = true;

  const formData = new FormData(document.getElementById("student-form"));

  $.ajax({
    method:      "POST",
    url:         url(`/students/api/edit?id=${id}`),
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
      setTimeout(function () { window.location.href = url("/students"); }, 1000);
    },
  });

  return false;
}

// ── Delete ────────────────────────────────────────────────────────────────────
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
    const response = await fetch(url(`/students/api/delete?id=${id}`), { method: "DELETE" });

    if (response.ok) {
      $("#modal-delete").modal("hide");
      showFlashAlert("Student deleted successfully.", "success");
      setTimeout(function () { window.location.reload(); }, 1200);
    } else {
      let message = "An error occurred while deleting.";
      try { const j = await response.json(); if (j && j.message) message = j.message; } catch (_) {}
      $("#modal-delete").modal("hide");
      showFlashAlert(message, response.status === 409 ? "warning" : "danger");
      $("#btn-yes-text").text("Yes, Delete").show();
      $("#btn-yes-spinner").hide();
      $("#btn-yes").removeAttr("disabled");
    }
  } catch (_) {
    $("#modal-delete").modal("hide");
    showFlashAlert("Network error. Please check your connection.", "danger");
    $("#btn-yes-text").text("Yes, Delete").show();
    $("#btn-yes-spinner").hide();
    $("#btn-yes").removeAttr("disabled");
  }
}

// ── CSV Upload ────────────────────────────────────────────────────────────────
function onCsvFileSelected(input) {
  const file = input.files[0];
  if (!file) return;

  const $name = $("#csv-file-name");
  const $btn  = $("#btn-upload-csv");

  // Basic client-side checks
  if (!file.name.toLowerCase().endsWith(".csv")) {
    $name.text("❌ Only .csv files are accepted.").removeClass("text-info").addClass("text-danger").show();
    $btn.prop("disabled", true);
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    $name.text("❌ File is too large (max 5 MB).").removeClass("text-info").addClass("text-danger").show();
    $btn.prop("disabled", true);
    return;
  }

  $name.text("✅ Selected: " + file.name + " (" + (file.size / 1024).toFixed(1) + " KB)")
       .removeClass("text-danger").addClass("text-info").show();
  $btn.prop("disabled", false);
  $("#csv-results-panel").hide();
}

function uploadCSV() {
  const fileInput = document.getElementById("csvFile");
  const file      = fileInput.files[0];
  if (!file) { alert("Please select a CSV file first."); return; }

  const $btn      = $("#btn-upload-csv");
  const $spinner  = $("#btn-upload-spinner");
  const $btnText  = $("#btn-upload-text");
  const $progWrap = $("#csv-progress-wrap");
  const $progBar  = $("#csv-progress-bar");
  const $progLbl  = $("#csv-progress-label");

  // Lock UI
  $btn.prop("disabled", true);
  $spinner.show();
  $btnText.text("Uploading...");
  $progWrap.show();
  animateProgress($progBar, $progLbl);
  $("#csv-results-panel").hide();

  const formData = new FormData();
  formData.append("csvFile", file);

  $.ajax({
    method:      "POST",
    url:         url("/students/api/csv-upload"),
    contentType: false,
    processData: false,
    data:        formData,
    success: function (data) {
      finishProgress($progBar, $progLbl);
      renderCSVResults(data);
    },
    error: function (xhr) {
      finishProgress($progBar, $progLbl, true);
      let msg = "Upload failed.";
      try { const j = JSON.parse(xhr.responseText); if (j && j.message) msg = j.message; } catch (_) {}
      showAlert(msg, "danger");
    },
    complete: function () {
      $btn.prop("disabled", false);
      $spinner.hide();
      $btnText.text("Upload & Import");
    },
  });
}

function animateProgress($bar, $lbl) {
  let w = 0;
  $bar.css("width", "0%");
  $lbl.text("Uploading file...");
  const interval = setInterval(function () {
    w = Math.min(w + Math.random() * 8, 85);
    $bar.css("width", w + "%");
    if (w >= 85) {
      clearInterval(interval);
      $lbl.text("Processing rows...");
    }
  }, 200);
  $bar.data("interval", interval);
}

function finishProgress($bar, $lbl, isError) {
  clearInterval($bar.data("interval"));
  $bar.css("width", "100%");
  if (isError) {
    $bar.removeClass("bg-info").addClass("bg-danger");
    $lbl.text("Upload failed.");
  } else {
    $bar.removeClass("bg-info").addClass("bg-success");
    $lbl.text("Done.");
  }
}

function renderCSVResults(data) {
  const imported = data.imported || 0;
  const skipped  = data.skipped  || 0;
  const errors   = data.errors   || [];

  const $summary = $("#csv-summary");
  $summary.html(`
    <div class="col-auto">
      <div class="info-box bg-success mb-0" style="min-width:140px;">
        <div class="info-box-content p-3">
          <span class="info-box-number text-white">${imported}</span>
          <span class="info-box-text text-white">Imported</span>
        </div>
      </div>
    </div>
    <div class="col-auto">
      <div class="info-box bg-danger mb-0" style="min-width:140px;">
        <div class="info-box-content p-3">
          <span class="info-box-number text-white">${skipped}</span>
          <span class="info-box-text text-white">Skipped</span>
        </div>
      </div>
    </div>
    <div class="col-auto">
      <div class="info-box bg-secondary mb-0" style="min-width:140px;">
        <div class="info-box-content p-3">
          <span class="info-box-number text-white">${imported + skipped}</span>
          <span class="info-box-text text-white">Total Rows</span>
        </div>
      </div>
    </div>
  `);

  if (errors.length > 0) {
    const $tbody = $("#csv-errors-body").empty();
    errors.forEach(function (e) {
      const msgs = Array.isArray(e.messages) ? e.messages.join("<br>") : e.messages;
      $tbody.append(`
        <tr>
          <td class="text-center font-weight-bold">${e.row}</td>
          <td class="text-danger">${msgs}</td>
        </tr>
      `);
    });
    $("#csv-errors-wrap").show();
  } else {
    $("#csv-errors-wrap").hide();
  }

  $("#csv-results-panel").show();
  $("html, body").animate({ scrollTop: $("#csv-results-panel").offset().top - 80 }, 400);
}
