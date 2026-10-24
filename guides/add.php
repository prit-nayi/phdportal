<?php

$title = "Add Guide - PhD Portal";
Authorize::forRolesOrRedirect([Role::ADMIN]);

require_once pathOf('/@/includes/header.php');

?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Add PhD Guide</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/guides') ?>">Guides</a></li>
              <li class="breadcrumb-item active">Add</li>
            </ol>
          </div>
        </div>
        <div class="row">&nbsp;</div>
      </div>
    </section>
    <section class="content">

      <!-- Alert placeholder -->
      <div id="form-alert" style="display:none;"></div>

      <div class="row">
        <div class="col-lg-10 offset-lg-1">
          <form id="guide-form" onsubmit="add(event);" novalidate>
            <div class="card card-outline card-info">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Guide Details</h3>
              </div>
              <div class="card-body">

                <!-- Guide Name & Designation -->
                <div class="row mt-2">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="name">Guide Name <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control"
                        id="name"
                        name="name"
                        placeholder="e.g. Dr. Ramesh Kumar"
                        maxlength="255"
                        required
                        autofocus>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="designation">Designation <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control"
                        id="designation"
                        name="designation"
                        placeholder="e.g. Professor / Associate Professor"
                        maxlength="255"
                        required>
                    </div>
                  </div>
                </div>

                <!-- Maximum Number of Scholars & Active Status -->
                <div class="row mt-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="maximumNumberOfScholars">Maximum Number of Scholars <span class="text-danger">*</span></label>
                      <input
                        type="number"
                        class="form-control"
                        id="maximumNumberOfScholars"
                        name="maximumNumberOfScholars"
                        placeholder="e.g. 8"
                        min="0"
                        step="1"
                        value="8"
                        required>
                      <small class="form-text text-muted">Maximum capacity of PhD scholars this guide can supervise (>= 0).</small>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="isActive">Status</label>
                      <select class="form-control" id="isActive" name="isActive">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                      </select>
                      <small class="form-text text-muted">Inactive guides cannot be allocated to new scholars.</small>
                    </div>
                  </div>
                </div>

              </div>
              <div class="card-footer text-right">
                <a href="<?= urlOf('/guides') ?>" class="btn btn-secondary mr-2">Cancel</a>
                <button type="submit" class="btn btn-success btn-submit" id="btn-submit">
                  <span class="btn-submit-text"><i class="fas fa-save mr-1"></i> Save Guide</span>
                  <span class="btn-submit-text-saved" style="display: none"><i class="fas fa-check mr-1"></i> Saved!</span>
                  <div class="btn-submit-spinner spinner-border spinner-border-sm" role="status" style="display: none">
                    <span class="sr-only">Saving...</span>
                  </div>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>
</div>

<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/guides/js/guides.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
