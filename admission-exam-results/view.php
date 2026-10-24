<?php

$title = "View Admission Exam Result";
Authorize::forRolesOrRedirect([Role::ADMIN]);

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
  header("Location: " . urlOf('/admission-exam-results'));
  exit;
}

$db = Database::getInstance();

$result = $db->get(
  "SELECT r.*, 
          s.StudentName, s.Surname, s.ApplicationNo, s.EmailStudent, s.MobileStudent,
          e.dateAndTimeOfWrittenExam, e.dateAndTimeOfPresentation, 
          e.maximumMarksOfWrittenExam, e.maximumMarksOfPresentation,
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

// Calculate totals and percentage if max marks available
$obtainedWritten = $result->obtainedMarksInWrittenExam !== null ? (float)$result->obtainedMarksInWrittenExam : 0;
$obtainedPresentation = $result->obtainedMarksInPresentation !== null ? (float)$result->obtainedMarksInPresentation : 0;
$totalObtained = $obtainedWritten + $obtainedPresentation;

$maxWritten = $result->maximumMarksOfWrittenExam !== null ? (float)$result->maximumMarksOfWrittenExam : 0;
$maxPresentation = $result->maximumMarksOfPresentation !== null ? (float)$result->maximumMarksOfPresentation : 0;
$totalMax = $maxWritten + $maxPresentation;

$percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : null;

function displayDateTime(?string $ts): string
{
  if (!$ts) return '—';
  return date('d M Y, h:i A', strtotime($ts));
}

require_once pathOf('/@/includes/header.php');

?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Admission Exam Result Details</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/admission-exam-results') ?>">Exam Results</a></li>
              <li class="breadcrumb-item active">View</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <!-- Flash Alert Placeholder -->
      <div id="flash-alert" style="display:none;"></div>

      <div class="row mb-3">
        <div class="col text-right">
          <a role="button" class="btn btn-secondary mr-1" href="<?= urlOf('/admission-exam-results') ?>">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
          </a>
          <a role="button" class="btn btn-primary mr-1" href="<?= urlOf("/admission-exam-results/edit?id={$result->id}") ?>">
            <i class="fas fa-pencil-alt mr-1"></i> Edit Result
          </a>
          <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= (int)$result->id ?>, '<?= htmlspecialchars(addslashes($result->StudentName . ' ' . $result->Surname), ENT_QUOTES) ?>');">
            <i class="fas fa-trash-alt mr-1"></i> Delete
          </button>
        </div>
      </div>

      <div class="row">
        <!-- Student Info Card -->
        <div class="col-md-6">
          <div class="card card-outline card-info h-100">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-user-graduate mr-2"></i> Student Information</h3>
            </div>
            <div class="card-body">
              <table class="table table-sm table-borderless">
                <tr>
                  <th style="width: 40%;">Full Name:</th>
                  <td><strong><?= htmlspecialchars($result->StudentName . ' ' . $result->Surname, ENT_QUOTES) ?></strong></td>
                </tr>
                <tr>
                  <th>Application No:</th>
                  <td><span class="badge badge-light border"><?= htmlspecialchars($result->ApplicationNo, ENT_QUOTES) ?></span></td>
                </tr>
                <tr>
                  <th>Email:</th>
                  <td><?= htmlspecialchars($result->EmailStudent ?? '—', ENT_QUOTES) ?></td>
                </tr>
                <tr>
                  <th>Mobile:</th>
                  <td><?= htmlspecialchars($result->MobileStudent ?? '—', ENT_QUOTES) ?></td>
                </tr>
                <tr>
                  <th>PhD Batch:</th>
                  <td><?= htmlspecialchars($result->batchName, ENT_QUOTES) ?> (<?= (int)$result->enrollmentYear ?>)</td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Exam Info Card -->
        <div class="col-md-6">
          <div class="card card-outline card-primary h-100">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-file-signature mr-2"></i> Admission Exam Information</h3>
            </div>
            <div class="card-body">
              <table class="table table-sm table-borderless">
                <tr>
                  <th style="width: 40%;">Batch Name:</th>
                  <td><?= htmlspecialchars($result->batchName, ENT_QUOTES) ?></td>
                </tr>
                <tr>
                  <th>Written Exam Date:</th>
                  <td><?= displayDateTime($result->dateAndTimeOfWrittenExam) ?></td>
                </tr>
                <tr>
                  <th>Presentation Date:</th>
                  <td><?= displayDateTime($result->dateAndTimeOfPresentation) ?></td>
                </tr>
                <tr>
                  <th>Max Written Marks:</th>
                  <td><?= $result->maximumMarksOfWrittenExam !== null ? htmlspecialchars($result->maximumMarksOfWrittenExam, ENT_QUOTES) : '—' ?></td>
                </tr>
                <tr>
                  <th>Max Presentation Marks:</th>
                  <td><?= $result->maximumMarksOfPresentation !== null ? htmlspecialchars($result->maximumMarksOfPresentation, ENT_QUOTES) : '—' ?></td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4">
        <!-- Performance & Result Card -->
        <div class="col-md-12">
          <div class="card card-outline card-success">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title mb-0"><i class="fas fa-poll mr-2"></i> Performance & Result Breakdown</h3>
              <div class="card-tools">
                <?php if ($result->hasPassed): ?>
                  <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 1.1em;">
                    <i class="fas fa-check-circle mr-1"></i> PASSED
                  </span>
                <?php else: ?>
                  <span class="badge badge-danger px-3 py-2 font-weight-bold" style="font-size: 1.1em;">
                    <i class="fas fa-times-circle mr-1"></i> FAILED
                  </span>
                <?php endif; ?>
              </div>
            </div>
            <div class="card-body">
              <div class="row text-center mb-4">
                <div class="col-md-4 border-right">
                  <h6 class="text-muted">Written Exam Marks</h6>
                  <h3 class="text-dark">
                    <?= $result->obtainedMarksInWrittenExam !== null ? htmlspecialchars($result->obtainedMarksInWrittenExam, ENT_QUOTES) : '—' ?>
                    <?php if ($result->maximumMarksOfWrittenExam !== null): ?>
                      <small class="text-muted" style="font-size: 0.5em;">/ <?= htmlspecialchars($result->maximumMarksOfWrittenExam, ENT_QUOTES) ?></small>
                    <?php endif; ?>
                  </h3>
                </div>
                <div class="col-md-4 border-right">
                  <h6 class="text-muted">Presentation Marks</h6>
                  <h3 class="text-dark">
                    <?= $result->obtainedMarksInPresentation !== null ? htmlspecialchars($result->obtainedMarksInPresentation, ENT_QUOTES) : '—' ?>
                    <?php if ($result->maximumMarksOfPresentation !== null): ?>
                      <small class="text-muted" style="font-size: 0.5em;">/ <?= htmlspecialchars($result->maximumMarksOfPresentation, ENT_QUOTES) ?></small>
                    <?php endif; ?>
                  </h3>
                </div>
                <div class="col-md-4">
                  <h6 class="text-muted">Total Score & Percentage</h6>
                  <h3 class="text-info">
                    <?= number_format($totalObtained, 2) ?>
                    <?php if ($totalMax > 0): ?>
                      <small class="text-muted" style="font-size: 0.5em;">/ <?= number_format($totalMax, 2) ?></small>
                      <span class="d-block text-muted" style="font-size: 0.45em;"><?= $percentage ?>%</span>
                    <?php endif; ?>
                  </h3>
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-6">
                  <h5><i class="fas fa-paperclip mr-2 text-muted"></i> Proposal Presentation File</h5>
                  <?php if (!empty($result->proposalPresentationFileName)): ?>
                    <p class="mb-1">File attached:</p>
                    <a href="<?= urlOf('/' . $result->proposalPresentationFileName) ?>" target="_blank" class="btn btn-outline-primary">
                      <i class="fas fa-file-download mr-1"></i> Download Proposal Presentation
                    </a>
                  <?php else: ?>
                    <p class="text-muted">No proposal presentation file uploaded for this result.</p>
                  <?php endif; ?>
                </div>
                <div class="col-md-6 text-md-right text-muted">
                  <p class="mb-1"><small>Recorded On: <?= displayDateTime($result->createdAt) ?></small></p>
                  <p class="mb-0"><small>Last Modified: <?= displayDateTime($result->updatedAt) ?></small></p>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

    </section>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-exclamation-triangle mr-2"></i> Confirm Delete</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-left">
        Are you sure you want to delete the exam result for <strong id="deleteStudentName"></strong>?
        <p class="text-danger mt-2 mb-0"><small>This action cannot be undone and associated files will be removed.</small></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deleteResult();">Delete</button>
      </div>
    </div>
  </div>
</div>

<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/admission-exam-results/js/exam-results.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
