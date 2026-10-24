function createErrorMessage(message) {
  const errorMessage = `<div class="alert alert-danger alert-dismissible fade show" role="alert">${message}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>`;
  return errorMessage;
}

function createSuccessMessage(message) {
  const successMessage = `<div class="alert alert-success alert-dismissible fade show" role="alert">${message}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>`;
  return successMessage;
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function url(path) {
  return window.__APP__.baseUrl + path;
}
