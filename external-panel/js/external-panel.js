var currentIdToDelete = null;

function showDeleteConfirmation(id) {
  currentIdToDelete = id;
  $("#modal-delete").modal("show");
}

function _delete() {
  if (!currentIdToDelete) return;

  $("#btn-yes").prop("disabled", true).text("Deleting...");

  $.ajax({
    method: "POST",
    url: url("/external-panel/api/delete"),
    data: { id: currentIdToDelete },
    error: function (xhr) {
      var message = "An error occurred";
      try {
        var json = JSON.parse(xhr.responseText);
        message = json.message || message;
      } catch (e) {}
      alert(message);
      $("#btn-yes").prop("disabled", false).text("Yes");
      $("#modal-delete").modal("hide");
    },
    success: function () {
      $("#modal-delete").modal("hide");
      window.location.reload();
    }
  });
}

function getFormData() {
  var fullName = $("#fullName").val().trim();
  var designation = $("#designation").val().trim();
  var organization = $("#organization").val().trim();
  var mobileNumber = $("#mobileNumber").val().trim();
  var emailAdderess = $("#emailAdderess").val().trim();

  var phoneRegex = /^\d{10}$/;
  if (!phoneRegex.test(mobileNumber)) {
    alert("Mobile number must be exactly 10 digits.");
    $("#mobileNumber").focus();
    return null;
  }

  var formData = new FormData();
  formData.append("fullName", fullName);
  formData.append("designation", designation);
  formData.append("organization", organization);
  formData.append("mobileNumber", mobileNumber);
  formData.append("emailAdderess", emailAdderess);

  return formData;
}

function add(event) {
  event.preventDefault();

  var formData = getFormData();
  if (!formData) return;

  $(".btn-submit").prop("disabled", true);
  $(".btn-submit-text").hide();
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();

  $.ajax({
    method: "POST",
    url: url("/external-panel/api/add"),
    data: formData,
    processData: false,
    contentType: false,
    error: function (xhr) {
      var message = "An error occurred";
      try {
        var json = JSON.parse(xhr.responseText);
        message = json.message || message;
      } catch (e) {}
      alert(message);
      $(".btn-submit-spinner").hide();
      $(".btn-submit").prop("disabled", false);
      $(".btn-submit-text").show();
    },
    success: function () {
      $(".btn-submit-spinner").hide();
      $(".btn-submit-text-saved").show();
      setTimeout(function () {
        window.location.href = url("/external-panel");
      }, 1500);
    }
  });
}

function edit(event, id) {
  event.preventDefault();

  var formData = getFormData();
  if (!formData) return;
  formData.append("id", id);

  $(".btn-submit").prop("disabled", true);
  $(".btn-submit-text").hide();
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();

  $.ajax({
    method: "POST",
    url: url("/external-panel/api/edit"),
    data: formData,
    processData: false,
    contentType: false,
    error: function (xhr) {
      var message = "An error occurred";
      try {
        var json = JSON.parse(xhr.responseText);
        message = json.message || message;
      } catch (e) {}
      alert(message);
      $(".btn-submit-spinner").hide();
      $(".btn-submit").prop("disabled", false);
      $(".btn-submit-text").show();
    },
    success: function () {
      $(".btn-submit-spinner").hide();
      $(".btn-submit-text-saved").show();
      setTimeout(function () {
        window.location.href = url("/external-panel");
      }, 1500);
    }
  });
}
