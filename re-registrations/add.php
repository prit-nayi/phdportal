<?php

$title = "Add Re-Registration";
Authorize::forRolesOrRedirect([Role::ADMIN]);

$db = Database::getInstance();

// Fetch only active, non-cancelled, non-transferred students for the dropdown
$students = $db->getAll(
  "SELECT s.`id`, s.`StudentName`, s.`Surname`, s.`ApplicationNo`,
          b.`name` AS batchName, b.`enrollmentYear`
   FROM `phdStudents` s
   LEFT JOIN `phdBatches` b ON s.`batchId` = b.`id`
   WHERE s.`isActive` = 1 AND s.`isCancelled` = 0 AND s.`isTransferred` = 0
   ORDER BY s.`StudentName` ASC, s.`Surname` ASC"
);

require_once pathOf('/@/includes/header.php');
?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6"><h1>Add Re-Registration</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/re-registrations') ?>">Re-Registrations</a></li>
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
        <div class="col">
          <form id="rereg-form" onsubmit="addReRegistration(event);" novalidate>
            <div class="card card-outline card-info">
              <div class="card-header">
                <h3 class="card-title">Re-Registration Details</h3>
              </div>
              <div class="card-body">

                <!-- Student Selection -->
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="studentId">Student <span class="text-danger">*</span></label>
                      <select class="form-control" id="studentId" name="studentId" required>
                        <option value="">— Select Student —</option>
                        <?php foreach ($students as $s): ?>
                          <option value="<?= (int)$s->id ?>">
                            <?= htmlspecialchars($s->StudentName . ' ' . $s->Surname, ENT_QUOTES) ?>
                            — <?= htmlspecialchars($s->ApplicationNo, ENT_QUOTES) ?>
                            <?php if ($s->batchName): ?>
                              (<?= htmlspecialchars($s->batchName, ENT_QUOTES) ?> <?= (int)$s->enrollmentYear ?>)
                            <?php endif; ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <small class="form-text text-muted">Only active students are shown.</small>
                    </div>
                  </div>
                </div>

                <!-- Reason -->
                <div class="row mt-2">
                  <div class="col">
                    <div class="form-group">
                      <label for="reason">Reason</label>
                      <textarea class="form-control" id="reason" name="reason" rows="3"
                        placeholder="e.g. Annual re-registration, Extension period, After leave of absence..."></textarea>
                      <small class="form-text text-muted">Optional. Describe the reason for re-registration.</small>
                    </div>
                  </div>
                </div>

              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-success btn-submit" id="btn-submit">
                  <span class="btn-submit-text">Submit</span>
                  <span class="btn-submit-text-saved" style="display: none">Saved!</span>
                  <div class="btn-submit-spinner spinner-border spinner-border-sm" role="status" style="display: none">
                    <span class="sr-only">Loading...</span>
                  </div>
                </button>
                <a href="<?= urlOf('/re-registrations') ?>" class="btn btn-outline-secondary ml-2">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>
</div>

<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/re-registrations/js/re-registrations.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
