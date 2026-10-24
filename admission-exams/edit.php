<?php

$title = "Edit Admission Exam";
Authorize::forRolesOrRedirect([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithRedirect('/admission-exams');
}

$id = (int) $_GET['id'];
$db = Database::getInstance();

$record = $db->get("SELECT * FROM `admissionExams` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithRedirect('/admission-exams');
}

$batches = $db->getAll("SELECT `id`, `name`, `enrollmentYear` FROM `phdBatches` ORDER BY `enrollmentYear` DESC, `name` ASC");

// Format dates for HTML5 datetime-local input (Y-m-dTH:i)
$writtenDateFormatted = $record->dateAndTimeOfWrittenExam ? date('Y-m-d\TH:i', strtotime($record->dateAndTimeOfWrittenExam)) : '';
$presentationDateFormatted = $record->dateAndTimeOfPresentation ? date('Y-m-d\TH:i', strtotime($record->dateAndTimeOfPresentation)) : '';

require_once pathOf('/@/includes/header.php');

?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit Admission Exam</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/admission-exams') ?>">Admission Exams</a></li>
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
          <form id="exam-form" onsubmit="editExam(<?= $id ?>, event);" novalidate>
            <div class="card card-outline card-info">
              <div class="card-body">

                <!-- Batch Selection -->
                <div class="form-group">
                  <label for="batchId">Batch <span class="text-danger">*</span></label>
                  <select class="form-control" id="batchId" name="batchId" required autofocus>
                    <option value="">-- Select Batch --</option>
                    <?php foreach ($batches as $batch): ?>
                      <option value="<?= (int) $batch->id ?>" <?= $batch->id == $record->batchId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($batch->name, ENT_QUOTES) ?> (<?= (int) $batch->enrollmentYear ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Written Exam & Presentation Dates -->
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="dateAndTimeOfWrittenExam">Written Exam Date & Time <span class="text-danger">*</span></label>
                      <input
                        type="datetime-local"
                        class="form-control"
                        id="dateAndTimeOfWrittenExam"
                        name="dateAndTimeOfWrittenExam"
                        value="<?= $writtenDateFormatted ?>"
                        required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="dateAndTimeOfPresentation">Presentation Date & Time <span class="text-danger">*</span></label>
                      <input
                        type="datetime-local"
                        class="form-control"
                        id="dateAndTimeOfPresentation"
                        name="dateAndTimeOfPresentation"
                        value="<?= $presentationDateFormatted ?>"
                        required>
                    </div>
                  </div>
                </div>

                <!-- Marks limits -->
                <div class="row mt-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="maximumMarksOfWrittenExam">Maximum Marks (Written Exam) <span class="text-danger">*</span></label>
                      <input
                        type="number"
                        class="form-control"
                        id="maximumMarksOfWrittenExam"
                        name="maximumMarksOfWrittenExam"
                        placeholder="e.g. 100.00"
                        step="0.01"
                        min="0.01"
                        value="<?= htmlspecialchars($record->maximumMarksOfWrittenExam, ENT_QUOTES) ?>"
                        required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="maximumMarksOfPresentation">Maximum Marks (Presentation) <span class="text-danger">*</span></label>
                      <input
                        type="number"
                        class="form-control"
                        id="maximumMarksOfPresentation"
                        name="maximumMarksOfPresentation"
                        placeholder="e.g. 50.00"
                        step="0.01"
                        min="0.01"
                        value="<?= htmlspecialchars($record->maximumMarksOfPresentation, ENT_QUOTES) ?>"
                        required>
                    </div>
                  </div>
                </div>

                <!-- Question Paper Name -->
                <div class="form-group mt-3">
                  <label for="writtenExamPaperFileName">Question Paper Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="writtenExamPaperFileName"
                    name="writtenExamPaperFileName"
                    placeholder="e.g. PhD Entrance Test 2025 Set-A"
                    value="<?= htmlspecialchars($record->writtenExamPaperFileName ?? '', ENT_QUOTES) ?>">
                </div>

                <!-- Audit info -->
                <div class="row mt-4">
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
                    <span class="sr-only">Saving...</span>
                  </div>
                </button>
                <a href="<?= urlOf('/admission-exams') ?>" class="btn btn-secondary">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>
</div>

<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/admission-exams/js/exams.js') ?>?v=<?= time() ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
