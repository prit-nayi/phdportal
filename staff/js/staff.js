function imageSelected() {
  const file = $("#image")[0].files[0];
  const image = URL.createObjectURL(file);

  $(".custom-file-label").text(`'${file.name}' selected.`);
  $(".preview-item").attr("src", image);
}

function clearImage(defaultImage = url("/@/assets/img/default-user.png")) {
  $("#image").val("");
  $(".custom-file-label").text("Select image...");
  $(".preview-item").attr("src", defaultImage);
}

function add(event) {
  event.preventDefault();
  $(".btn-submit").attr("disabled", "");

  $(".btn-submit-text").hide();
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();

  const name = $("#name").val();
  const staffType = $("#staffType").val();
  const emailAddress = $("#emailAddress").val();
  const designation = $("#designation").val();
  const qualification = $("#qualification").val();
  const experience = $("#experience").val();
  const specialization = $("#specialization").val();
  const researchArea = $("#researchArea").val();
  const briefDescription = $("#briefDescription").val();
  const websiteLink = $("#websiteLink").val();
  const googleScholarLink = $("#googleScholarLink").val();
  const linkedinLink = $("#linkedinLink").val();
  const researchgateLink = $("#researchgateLink").val();
  const orcidLink = $("#orcidLink").val();
  const scopusLink = $("#scopusLink").val();
  const image = $("#image")[0].files[0];

  const formData = new FormData();
  formData.append("name", name);
  formData.append("staffType", staffType);
  formData.append("emailAddress", emailAddress);
  formData.append("designation", designation);
  formData.append("qualification", qualification);
  formData.append("experience", experience);
  formData.append("specialization", specialization);
  formData.append("researchArea", researchArea);
  formData.append("briefDescription", briefDescription);
  if (websiteLink != "") formData.append("websiteLink", websiteLink);
  if (googleScholarLink != "")
    formData.append("googleScholarLink", googleScholarLink);
  if (linkedinLink != "") formData.append("linkedinLink", linkedinLink);
  if (researchgateLink != "")
    formData.append("researchgateLink", researchgateLink);
  if (orcidLink != "") formData.append("orcidLink", orcidLink);
  if (scopusLink != "") formData.append("scopusLink", scopusLink);
  formData.append("image", image);

  $.ajax({
    method: "POST",
    url: url("/staff/api/add"),
    contentType: false,
    processData: false,
    data: formData,
    error: (xhr) => {
      alert(xhr.responseText || "An error occurred");
      $(".btn-submit-spinner").hide();
      $(".btn-submit").removeAttr("disabled");
      $(".btn-submit-text").show();
    },
    success: () => {
      $(".btn-submit-text").hide();
      $(".btn-submit-text-saved").show();
      $(".btn-submit-spinner").hide();

      setTimeout(() => {
        window.location.href = "../staff";
      }, 1000);
    },
  });

  return false;
}

function edit(id, event) {
  event.preventDefault();
  $(".btn-submit").attr("disabled", "");

  $(".btn-submit-text").hide();
  $(".btn-submit-text-saved").hide();
  $(".btn-submit-spinner").show();

  const name = $("#name").val();
  const staffType = $("#staffType").val();
  const emailAddress = $("#emailAddress").val();
  const designation = $("#designation").val();
  const qualification = $("#qualification").val();
  const experience = $("#experience").val();
  const specialization = $("#specialization").val();
  const researchArea = $("#researchArea").val();
  const briefDescription = $("#briefDescription").val();
  const websiteLink = $("#websiteLink").val();
  const googleScholarLink = $("#googleScholarLink").val();
  const linkedinLink = $("#linkedinLink").val();
  const researchgateLink = $("#researchgateLink").val();
  const orcidLink = $("#orcidLink").val();
  const scopusLink = $("#scopusLink").val();

  const formData = new FormData();
  formData.append("name", name);
  formData.append("staffType", staffType);
  formData.append("emailAddress", emailAddress);
  formData.append("designation", designation);
  formData.append("qualification", qualification);
  formData.append("experience", experience);
  formData.append("specialization", specialization);
  formData.append("researchArea", researchArea);
  formData.append("briefDescription", briefDescription);
  if (websiteLink != "") formData.append("websiteLink", websiteLink);
  if (googleScholarLink != "")
    formData.append("googleScholarLink", googleScholarLink);
  if (linkedinLink != "") formData.append("linkedinLink", linkedinLink);
  if (researchgateLink != "")
    formData.append("researchgateLink", researchgateLink);
  if (orcidLink != "") formData.append("orcidLink", orcidLink);
  if (scopusLink != "") formData.append("scopusLink", scopusLink);

  const images = $("#image")[0].files;
  if (images.length > 0) {
    const image = images[0];
    formData.append("image", image);
  }

  $.ajax({
    method: "POST",
    url: url(`/staff/api/edit?id=${id}`),
    contentType: false,
    processData: false,
    data: formData,
    error: (xhr) => {
      alert(xhr.responseText || "An error occurred");
      $(".btn-submit-spinner").hide();
      $(".btn-submit").removeAttr("disabled");
      $(".btn-submit-text").show();
    },
    success: () => {
      $(".btn-submit-text").hide();
      $(".btn-submit-text-saved").show();
      $(".btn-submit-spinner").hide();

      setTimeout(() => {
        window.location.href = url("/staff");
      }, 1000);
    },
  });

  return false;
}

function changeStatus(event, button, id) {
  event.preventDefault();

  text = $(button).find(".btn-submit-text");
  spinner = $(button).find(".btn-submit-spinner");

  $(button).attr("disabled", "");
  $(text).hide();
  $(spinner).show();

  currentStatus = $(button).attr("data-status");
  newStatus = currentStatus === "active" ? "inactive" : "active";
  newStatusText = newStatus === "active" ? "Deactivate" : "Activate";

  newClass = newStatus === "active" ? "btn-warning" : "btn-success";

  $.ajax({
    method: "GET",
    url: url(`/staff/api/change-status?id=${id}`),
    success: () => {
      $(button).removeClass(
        currentStatus === "active" ? "btn-warning" : "btn-success",
      );
      $(button).removeAttr("disabled");
      $(button).attr("data-status", newStatus);
      $(button).addClass(newClass);
      $(text).text(newStatusText);
      $(text).show();
      $(spinner).hide();
    },
  });

  return false;
}

function showDeleteConfirmation(id) {
  $("#btn-yes").attr("data-id", id);
  $("#modal-delete").modal("show");
}

async function _delete() {
  const id = $("#btn-yes").attr("data-id");
  if (id == null) return;

  const response = await fetch(url(`/staff/api/delete?id=${id}`), {
    method: "DELETE",
  });
  if (response.ok) {
    window.location.href = url("/staff");
  }
}

async function deleteImage(id) {
  if (!confirm("Are you sure you want to remove the profile picture?")) return;

  const response = await fetch(url(`/staff/api/delete-image`), {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${id}`,
  });

  if (response.ok) {
    window.location.reload();
  } else {
    alert("Failed to delete profile picture.");
  }
}
