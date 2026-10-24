<?php

$title = "Edit Re-Registration";
Authorize::forRolesOrRedirect([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithRedirect('/re-registrations');
}

$id = (int) $_GET['id'];
$db = Database::getInstance();

// Fetch re-registration with student & batch info
$rec = $db->get(
  "SELECT r.*, s.`StudentName`, s.`Surname`, s.`ApplicationNo`,
          s.`isActive`, s.`isCancelled`, s.`isTransferred`,
          b.`name` AS batchName, b.`enrollmentYear`
   FROM `phdReRegistrations` r
   LEFT JOIN `phdStudents` s ON r.`studentId` = s.`id`
   LEFT JOIN `phdBatches` b ON s.`batchId` = b.`id`
   WHERE r.`id` = ?",
  [$id]
);

if (!$rec) {
  exitWithRedirect('/re-registrations');
}

// Helper: student status label
function studentStatus(object $rec): string
{
  if ($rec->isCancelled)   return '<span class="badge badge-danger">Cancelled</span>';
  if ($rec->isTransferred) return '<span class="badge badge-warning">Transferred</span>';
  if (!$rec->isActive)     return '<span class="badge badge-secondary">Inactive</span>';
  return '<span class="badge badge-success">Active</span>';
}

require_once pathOf('/@/includes/header.php');
?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6"><h1>Edit Re-Registration</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/re-registrations') ?>">Re-Registrations</a></li>
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
          <form id="rereg-form" onsubmit="editReRegistration(<?= $id ?>, event);" novalidate>
            <div class="card card-outline card-info">
              <div class="card-header">
                <h3 class="card-title">Re-Registration Details</h3>
              </div>
              <div class="card-body">

                <!-- Student Info (read-only) -->
                <div class="row mt-3">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Student</label>
                      <input type="text" class="form-control" readonly
                        value="<?= htmlspecialchars(($rec->StudentName ?? '') . ' ' . ($rec->Surname ?? ''), ENT_QUOTES) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Application No</label>
                      <input type="text" class="form-control" readonly
                        value="<?= htmlspecialchars($rec->ApplicationNo ?? '—', ENT_QUOTES) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Batch</label>
                      <input type="text" class="form-control" readonly
                        value="<?php if ($rec->batchName): ?><?= htmlspecialchars($rec->batchName, ENT_QUOTES) ?> (<?= (int)$rec->enrollmentYear ?>)<?php else: ?>—<?php endif; ?>">
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label>Status</label>
                      <div class="form-control border-0 px-0" style="background:transparent;">
                        <?= studentStatus($rec) ?>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Reason (editable) -->
                <div class="row mt-2">
                  <div class="col">
                    <div class="form-group">
                      <label for="reason">Reason</label>
                      <textarea class="form-control" id="reason" name="reason" rows="3"
                        placeholder="e.g. Annual re-registration, Extension period, After leave of absence..."><?= htmlspecialchars($rec->reason ?? '', ENT_QUOTES) ?></textarea>
                      <small class="form-text text-muted">Optional. Describe the reason for re-registration.</small>
                    </div>
                  </div>
                </div>

                <!-- Audit Info -->
                <div class="row mt-3">
                  <div class="col">
                    <small class="text-muted">
                      <i class="fas fa-calendar-plus mr-1"></i>
                      Created: <?= date('d M Y, h:i A', strtotime($rec->createdAt)) ?>
                      &nbsp;&nbsp;
                      <i class="fas fa-calendar-check mr-1"></i>
                      Last Updated: <?= date('d M Y, h:i A', strtotime($rec->updatedAt)) ?>
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
