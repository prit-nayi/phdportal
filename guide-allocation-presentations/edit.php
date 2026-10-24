<?php

$title = "Edit Presentation Schedule - PhD Portal";
Authorize::forRolesOrRedirect([Role::ADMIN]);

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
  exitWithRedirect('/guide-allocation-presentations');
}

$id = (int) $_GET['id'];
$db = Database::getInstance();

$presentation = $db->get("SELECT * FROM `guideAllocationPresentations` WHERE `id` = ?", [$id]);

if (!$presentation) {
  http_response_code(404);
  require_once pathOf('/404.php');
  exit();
}

$batches = $db->getAll("SELECT * FROM `phdBatches` ORDER BY `enrollmentYear` DESC, `name` ASC");

require_once pathOf('/@/includes/header.php');

?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit Presentation Schedule</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/guide-allocation-presentations') ?>">Presentation Schedule</a></li>
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
        <div class="col-lg-10 offset-lg-1">
          <form id="presentation-form" onsubmit="edit(<?= (int) $presentation->id ?>, event);" novalidate>
            <div class="card card-outline card-info">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-edit mr-2"></i>Edit Presentation Schedule Details</h3>
              </div>
              <div class="card-body">

                <!-- Batch Selection -->
                <div class="row mt-2">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="batchId">PhD Batch <span class="text-danger">*</span></label>
                      <select class="form-control" id="batchId" name="batchId" required autofocus>
                        <option value="">-- Select Batch --</option>
                        <?php foreach ($batches as $batch): ?>
                          <option value="<?= (int) $batch->id ?>" <?= (int) $presentation->batchId === (int) $batch->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($batch->name, ENT_QUOTES) ?> (<?= (int) $batch->enrollmentYear ?>)
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <!-- Presentation Name -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="name">Presentation Name <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control"
                        id="name"
                        name="name"
                        placeholder="e.g. Guide Allocation Presentation 2026"
                        maxlength="255"
                        value="<?= htmlspecialchars($presentation->name, ENT_QUOTES) ?>"
                        required>
                    </div>
                  </div>
                </div>

                <!-- Scheduled On Date -->
                <div class="row mt-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="scheduledOn">Scheduled Date <span class="text-danger">*</span></label>
                      <input
                        type="date"
                        class="form-control"
                        id="scheduledOn"
                        name="scheduledOn"
                        value="<?= htmlspecialchars($presentation->scheduledOn, ENT_QUOTES) ?>"
                        required>
                    </div>
                  </div>
                </div>

              </div>
              <div class="card-footer text-right">
                <a href="<?= urlOf('/guide-allocation-presentations') ?>" class="btn btn-secondary mr-2">Cancel</a>
                <button type="submit" class="btn btn-success btn-submit" id="btn-submit">
                  <span class="btn-submit-text"><i class="fas fa-save mr-1"></i> Update Presentation Schedule</span>
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
<script src="<?= urlOf('/guide-allocation-presentations/js/guide-allocation-presentations.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
