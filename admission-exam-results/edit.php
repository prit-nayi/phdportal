<?php

$title = "Edit Admission Exam Result";
Authorize::forRolesOrRedirect([Role::ADMIN]);

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
  header("Location: " . urlOf('/admission-exam-results'));
  exit;
}

$db = Database::getInstance();

$result = $db->get(
  "SELECT r.*, 
          s.StudentName, s.Surname, s.ApplicationNo, s.batchId AS studentBatchId,
          e.batchId AS examBatchId, e.maximumMarksOfWrittenExam, e.maximumMarksOfPresentation,
          e.dateAndTimeOfWrittenExam,
          b.name AS batchName, b.enrollmentYear
   FROM `admissionExamResults` r
   JOIN `phdStudents` s ON r.`studentId` = s.`id`
   JOIN `admissionExams` e ON r.`examId` = e.`id`
   JOIN `phdBatches` b ON e.`batchId` = b.`id`
   WHERE r.`id` = ?",
  [$id]
);

if (!$result) {
  header("Location: " . urlOf('/admission-exam-results'));
  exit;
}

// Fetch Admission Exams
$exams = $db->getAll(
  "SELECT e.`id`, e.`batchId`, e.`dateAndTimeOfWrittenExam`, e.`maximumMarksOfWrittenExam`, e.`maximumMarksOfPresentation`,
          b.`name` AS batchName, b.`enrollmentYear`
   FROM `admissionExams` e
   JOIN `phdBatches` b ON e.`batchId` = b.`id`
   ORDER BY e.`createdAt` DESC"
);

// Fetch Students
$students = $db->getAll(
  "SELECT s.`id`, s.`StudentName`, s.`Surname`, s.`ApplicationNo`, s.`batchId`,
          b.`name` AS batchName
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
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit Admission Exam Result</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/admission-exam-results') ?>">Exam Results</a></li>
              <li class="breadcrumb-item active">Edit</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <!-- Alert placeholder -->
      <div id="form-alert" style="display:none;"></div>

      <div class="row">
        <div class="col">
          <form id="result-form" onsubmit="editResult(<?= (int) $result->id ?>, event);" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id" value="<?= (int) $result->id ?>">

            <div class="card card-outline card-info">
              <div class="card-header">
                <h3 class="card-title">Edit Result: <?= htmlspecialchars($result->StudentName . ' ' . $result->Surname, ENT_QUOTES) ?></h3>
              </div>
              <div class="card-body">

                <div class="row">
                  <!-- Exam Selection -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="examId">Admission Exam <span class="text-danger">*</span></label>
                      <select class="form-control" id="examId" name="examId" required>
                        <option value="">-- Select Admission Exam --</option>
                        <?php foreach ($exams as $exam): ?>
                          <option value="<?= (int) $exam->id ?>"
                                  data-max-written="<?= $exam->maximumMarksOfWrittenExam !== null ? (float)$exam->maximumMarksOfWrittenExam : '' ?>"
                                  data-max-pres="<?= $exam->maximumMarksOfPresentation !== null ? (float)$exam->maximumMarksOfPresentation : '' ?>"
                                  data-batch-id="<?= (int)$exam->batchId ?>"
                                  <?= (int)$exam->id === (int)$result->examId ? 'selected' : '' ?>>
                            Batch: <?= htmlspecialchars($exam->batchName, ENT_QUOTES) ?> (<?= (int)$exam->enrollmentYear ?>) — <?= $exam->dateAndTimeOfWrittenExam ? date('d M Y', strtotime($exam->dateAndTimeOfWrittenExam)) : 'Scheduled' ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <!-- Student Selection -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="studentId">Student <span class="text-danger">*</span></label>
                      <select class="form-control" id="studentId" name="studentId" required>
                        <option value="">-- Select Student --</option>
                        <?php foreach ($students as $stu): ?>
                          <option value="<?= (int) $stu->id ?>"
                                  data-batch-id="<?= (int)$stu->batchId ?>"
                                  <?= (int)$stu->id === (int)$result->studentId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($stu->StudentName . ' ' . $stu->Surname, ENT_QUOTES) ?> (App: <?= htmlspecialchars($stu->ApplicationNo, ENT_QUOTES) ?><?= $stu->batchName ? ' | Batch: ' . htmlspecialchars($stu->batchName, ENT_QUOTES) : '' ?>)
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Marks Row -->
                <div class="row mt-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="obtainedMarksInWrittenExam">
                        Obtained Marks in Written Exam
                        <span id="max-written-hint" class="text-info font-weight-normal ml-1">
                          <?= $result->maximumMarksOfWrittenExam !== null ? '(Max: ' . (float)$result->maximumMarksOfWrittenExam . ')' : '' ?>
                        </span>
                      </label>
                      <input
                        type="number"
                        class="form-control"
                        id="obtainedMarksInWrittenExam"
                        name="obtainedMarksInWrittenExam"
                        value="<?= $result->obtainedMarksInWrittenExam !== null ? htmlspecialchars($result->obtainedMarksInWrittenExam, ENT_QUOTES) : '' ?>"
                        step="0.01"
                        min="0"
                        <?= $result->maximumMarksOfWrittenExam !== null ? 'max="' . (float)$result->maximumMarksOfWrittenExam . '"' : '' ?>>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="obtainedMarksInPresentation">
                        Obtained Marks in Presentation
                        <span id="max-pres-hint" class="text-info font-weight-normal ml-1">
                          <?= $result->maximumMarksOfPresentation !== null ? '(Max: ' . (float)$result->maximumMarksOfPresentation . ')' : '' ?>
                        </span>
                      </label>
                      <input
                        type="number"
                        class="form-control"
                        id="obtainedMarksInPresentation"
                        name="obtainedMarksInPresentation"
                        value="<?= $result->obtainedMarksInPresentation !== null ? htmlspecialchars($result->obtainedMarksInPresentation, ENT_QUOTES) : '' ?>"
                        step="0.01"
                        min="0"
                        <?= $result->maximumMarksOfPresentation !== null ? 'max="' . (float)$result->maximumMarksOfPresentation . '"' : '' ?>>
                    </div>
                  </div>
                </div>

                <!-- Status & Proposal File -->
                <div class="row mt-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="proposalPresentationFile">
                        Proposal Presentation File
                      </label>

                      <?php if (!empty($result->proposalPresentationFileName)): ?>
                        <div class="mb-2">
                          <span class="text-muted mr-2">Current File:</span>
                          <a href="<?= urlOf('/' . $result->proposalPresentationFileName) ?>" target="_blank" class="btn btn-xs btn-outline-info">
                            <i class="fas fa-file-download mr-1"></i> View / Download Current Proposal File
                          </a>
                        </div>
                      <?php endif; ?>

                      <input
                        type="file"
                        class="form-control-file"
                        id="proposalPresentationFile"
                        name="proposalPresentationFile"
                        accept=".pdf,.ppt,.pptx,.doc,.docx">
                      <small class="form-text text-muted">Upload a new file to replace the existing one (Optional, Max 10MB: .pdf, .ppt, .pptx, .doc, .docx).</small>
                    </div>
                  </div>

                  <div class="col-md-6 d-flex align-items-center mt-3 mt-md-0">
                    <div class="form-group mb-0">
                      <div class="custom-control custom-checkbox custom-control-lg">
                        <input type="checkbox" class="custom-control-input" id="hasPassed" name="hasPassed" value="1" <?= $result->hasPassed ? 'checked' : '' ?>>
                        <label class="custom-control-label font-weight-bold text-success" for="hasPassed">
                          Mark Student as Passed
                        </label>
                      </div>
                      <small class="form-text text-muted">Check this if the student successfully cleared the admission exam.</small>
                    </div>
                  </div>
                </div>

              </div>
              <div class="card-footer text-right">
                <a href="<?= urlOf('/admission-exam-results') ?>" class="btn btn-secondary mr-2">Cancel</a>
                <button type="submit" class="btn btn-primary btn-submit">
                  <span class="btn-submit-spinner spinner-border spinner-border-sm mr-1" style="display:none;" role="status"></span>
                  <span class="btn-submit-text"><i class="fas fa-save mr-1"></i> Update Result</span>
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
<script src="<?= urlOf('/admission-exam-results/js/exam-results.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
