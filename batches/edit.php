<?php

$title = "Edit Batch";

Authorize::forRolesOrRedirect([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithRedirect('/batches');
}

$id = (int) $_GET['id'];
$db = Database::getInstance();
$record = $db->get("SELECT * FROM `phdBatches` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithRedirect('/batches');
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
            <h1>Edit Batch</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/batches') ?>">Batches</a></li>
              <li class="breadcrumb-item active">Edit</li>
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
        <div class="col">
          <form id="batch-form" onsubmit="edit(<?= $id ?>, event);" novalidate>
            <div class="card card-outline card-info">
              <div class="card-body">

                <!-- Batch Name -->
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="name">Batch Name</label>
                      <input
                        type="text"
                        class="form-control"
                        id="name"
                        name="name"
                        placeholder="e.g. PhD Batch 2024"
                        value="<?= htmlspecialchars($record->name, ENT_QUOTES) ?>"
                        required
                        autofocus>
                    </div>
                  </div>
                </div>

                <!-- Enrollment Year + Fee Amount -->
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="enrollmentYear">Enrollment Year</label>
                      <input
                        type="number"
                        class="form-control"
                        id="enrollmentYear"
                        name="enrollmentYear"
                        placeholder="e.g. <?= date('Y') ?>"
                        value="<?= (int) $record->enrollmentYear ?>"
                        required>
                    </div>
                  </div>
                  <div class="col">
                    <div class="form-group">
                      <label for="feeAmount">Fee Amount (₹)</label>
                      <input
                        type="number"
                        class="form-control"
                        id="feeAmount"
                        name="feeAmount"
                        placeholder="e.g. 50000.00"
                        step="any"
                        value="<?= htmlspecialchars($record->feeAmount, ENT_QUOTES) ?>"
                        required>
                    </div>
                  </div>
                </div>

                <!-- Audit Info -->
                <div class="row mt-3">
                  <div class="col">
                    <small class="text-muted">
                      <i class="fas fa-calendar-plus mr-1"></i>
                      Created: <?= date('d M Y, h:i A', strtotime($record->createdAt)) ?>
                      &nbsp;&nbsp;
                      <i class="fas fa-calendar-check mr-1"></i>
                      Last Updated: <?= date('d M Y, h:i A', strtotime($record->updatedAt)) ?>
                    </small>
                  </div>
                </div>

              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-success btn-submit" id="btn-submit">
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
<script src="<?= urlOf('/batches/js/batches.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
