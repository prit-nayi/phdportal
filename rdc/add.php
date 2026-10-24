<?php

$title = "Add RDC";
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
            <h1>Add RDC</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/rdc') ?>">RDC List</a></li>
              <li class="breadcrumb-item active">Add</li>
            </ol>
          </div>
        </div>
        <div class="row">&nbsp;</div>
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class="col">
          <form onsubmit="add(event);">
            <div class="card card-outline card-info">
              <div class="card-body">
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" placeholder="Enter RDC name" required autofocus>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="scheduledOn">Scheduled On</label>
                      <input type="date" class="form-control" id="scheduledOn" min="<?= date('Y-m-d') ?>" required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-success btn-submit">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" placeholder="Enter RDC name" required autofocus>
                    </div>
                  </div>
                </div>

                <!-- Enrollment Year + Fee Amount -->
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="scheduledOn">Scheduled On</label>
                      <input type="date" class="form-control" id="scheduledOn" min="<?= date('Y-m-d') ?>" required>
                    </div>
                  </div>
                </div>

              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-success btn-submit" id="btn-submit">
                  <span class="btn-submit-text">Submit</span>
                  <span class="btn-submit-text-saved" style="display: none">Added!</span>
                  <div class="btn-submit-spinner spinner-border spinner-border-sm" role="status" style="display: none">
                    <span class="sr-only">Loading...</span>
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
<script src="<?= urlOf('/rdc/js/rdc.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
