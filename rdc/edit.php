<?php

$title = "Edit RDC";

Authorize::forRolesOrRedirect([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithRedirect('/rdc');
}

$id = get('id');
$db = Database::getInstance();
$record = $db->get("SELECT * FROM `scheduledRdcs` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithRedirect('/rdc');
}

require_once pathOf('/@/includes/header.php');

?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit RDC</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/rdc') ?>">RDC List</a></li>
              <li class="breadcrumb-item active">Edit</li>
            </ol>
          </div>
        </div>
        <div class="row">&nbsp;</div>
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class="col">
          <form onsubmit="edit(<?= $id ?>, event);">
            <div class="card card-outline card-info">
              <div class="card-body">
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" placeholder="Enter RDC name"
                        value="<?= htmlspecialchars($record->name) ?>" required autofocus>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="scheduledOn">Scheduled On</label>
                      <input type="date" class="form-control" id="scheduledOn"
                        value="<?= htmlspecialchars($record->scheduledOn) ?>"
                        min="<?= date('Y-m-d') ?>" required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-success btn-submit">
                  <span class="btn-submit-text">Submit</span>
                  <span class="btn-submit-text-saved" style="display: none">Updated!</span>
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
