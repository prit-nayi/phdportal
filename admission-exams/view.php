<?php

$title = "View Admission Exam Details";
Authorize::forRolesOrRedirect([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithRedirect('/admission-exams');
}

$id = (int) $_GET['id'];
$db = Database::getInstance();

$record = $db->get(
  "SELECT e.*, b.name AS batchName, b.enrollmentYear 
   FROM `admissionExams` e
   JOIN `phdBatches` b ON e.batchId = b.id
   WHERE e.id = ?",
  [$id]
);

if (!$record) {
  exitWithRedirect('/admission-exams');
}

// Date helpers
function formatDateTime(?string $dt): string
{
  if (!$dt) return '—';
  return date('d M Y, h:i A', strtotime($dt));
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
            <h1>Admission Exam Details</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/admission-exams') ?>">Admission Exams</a></li>
              <li class="breadcrumb-item active">View Details</li>
            </ol>
          </div>
        </div>
        <div class="row">&nbsp;</div>
      </div>
    </section>

    <section class="content">
      <div class="row">
        <div class="col">
          <div class="card card-outline card-info">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-file-signature mr-2"></i>
                Batch: <?= htmlspecialchars($record->batchName, ENT_QUOTES) ?> (<?= (int) $record->enrollmentYear ?>)
              </h3>
            </div>
            <div class="card-body">
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <th style="width: 30%">Written Exam Date & Time</th>
                    <td><?= formatDateTime($record->dateAndTimeOfWrittenExam) ?></td>
                  </tr>
                  <tr>
                    <th>Presentation Date & Time</th>
                    <td><?= formatDateTime($record->dateAndTimeOfPresentation) ?></td>
                  </tr>
                  <tr>
                    <th>Maximum Written Marks</th>
                    <td><strong><?= htmlspecialchars($record->maximumMarksOfWrittenExam, ENT_QUOTES) ?></strong> marks</td>
                  </tr>
                  <tr>
                    <th>Maximum Presentation Marks</th>
                    <td><strong><?= htmlspecialchars($record->maximumMarksOfPresentation, ENT_QUOTES) ?></strong> marks</td>
                  </tr>
                  <tr>
                    <th>Question Paper</th>
                    <td>
                      <?php if ($record->writtenExamPaperFileName !== '' && $record->writtenExamPaperFileName !== null): ?>
                        <?= htmlspecialchars($record->writtenExamPaperFileName, ENT_QUOTES) ?>
                      <?php else: ?>
                        <span class="text-muted">Not specified</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th>Date Created</th>
                    <td><?= formatDateTime($record->createdAt) ?></td>
                  </tr>
                  <tr>
                    <th>Last Updated</th>
                    <td><?= formatDateTime($record->updatedAt) ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="card-footer">
              <a href="<?= urlOf("/admission-exams/edit?id={$record->id}") ?>" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i> Edit Exam
              </a>
              <a href="<?= urlOf('/admission-exams') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<?php require_once pathOf('/@/includes/scripts.php'); ?>
<?php require_once pathOf('/@/includes/footer.php'); ?>
