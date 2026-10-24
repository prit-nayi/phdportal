<?php

$title = "Add Admission Exam Result";
Authorize::forRolesOrRedirect([Role::ADMIN]);

$db = Database::getInstance();

// Fetch Admission Exams
$exams = $db->getAll(
  "SELECT e.`id`, e.`batchId`, e.`dateAndTimeOfWrittenExam`, e.`maximumMarksOfWrittenExam`, e.`maximumMarksOfPresentation`,
          b.`name` AS batchName, b.`enrollmentYear`
   FROM `admissionExams` e
   JOIN `phdBatches` b ON e.`batchId` = b.`id`
   ORDER BY e.`createdAt` DESC"
);

require_once pathOf('/@/includes/header.php');

?>
<style>
  /* Sticky Table Header */
  .table-responsive-sticky {
    max-height: 520px;
    overflow-y: auto;
  }

  .table-responsive-sticky table thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 2;
    box-shadow: inset 0 -1px 0 #dee2e6;
  }

  /* Fixed/Sticky Bottom Card Footer */
  .sticky-card-footer {
    position: sticky;
    bottom: 0;
    background: #ffffff;
    z-index: 10;
    box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.05);
    border-top: 1px solid #dee2e6;
  }

  .nav-tabs .nav-link {
    font-weight: 600;
    padding: 0.65rem 1.25rem;
    color: #495057;
    border-top-left-radius: 0.35rem;
    border-top-right-radius: 0.35rem;
  }

  .nav-tabs .nav-link.active {
    color: #007bff;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
    border-bottom: 3px solid #007bff;
  }

  .is-invalid-mark {
    border-color: #dc3545 !important;
    background-color: #fff8f8 !important;
  }
</style>

<div class="content-wrapper">
  <div class="container-fluid px-4">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold">Add Admission Exam Result</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/admission-exam-results') ?>">Exam Results</a></li>
              <li class="breadcrumb-item active">Add</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <!-- Alert placeholder -->
      <div id="form-alert" style="display:none;"></div>

      <!-- Batch Selector Card -->
      <div class="card card-outline card-primary mb-4">
        <div class="card-body">
          <div class="form-group mb-0">
            <label for="examId" class="font-weight-bold">Select Batch / Admission Exam <span class="text-danger">*</span></label>
            <select class="form-control form-control-lg" id="examId" name="examId" required autofocus>
              <option value="">-- Select Admission Exam --</option>
              <?php foreach ($exams as $exam): ?>
                <option value="<?= (int) $exam->id ?>"
                        data-max-written="<?= $exam->maximumMarksOfWrittenExam !== null ? (float)$exam->maximumMarksOfWrittenExam : '' ?>"
                        data-max-pres="<?= $exam->maximumMarksOfPresentation !== null ? (float)$exam->maximumMarksOfPresentation : '' ?>">
                  Batch: <?= htmlspecialchars($exam->batchName, ENT_QUOTES) ?> (<?= (int)$exam->enrollmentYear ?>) — <?= $exam->dateAndTimeOfWrittenExam ? date('d M Y', strtotime($exam->dateAndTimeOfWrittenExam)) : 'Scheduled' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Main Students Container (Initially Hidden until exam selected) -->
      <div id="students-container" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <h4 class="font-weight-bold mb-0">Students</h4>
            <p class="text-muted small mb-0">Enter marks for the selected batch students.</p>
          </div>
        </div>

        <!-- Nav Tabs -->
        <ul class="nav nav-tabs id-tabs mb-0" id="exam-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="written-tab-link" data-toggle="tab" href="#written-tab" role="tab" aria-controls="written-tab" aria-selected="true">
              <i class="far fa-edit mr-2"></i> Written Exam Marks
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="presentation-tab-link" data-toggle="tab" href="#presentation-tab" role="tab" aria-controls="presentation-tab" aria-selected="false">
              <i class="fas fa-desktop mr-2"></i> Presentation Marks
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="ppt-tab-link" data-toggle="tab" href="#ppt-tab" role="tab" aria-controls="ppt-tab" aria-selected="false">
              <i class="fas fa-file-powerpoint mr-2"></i> Presentation PPT
            </a>
          </li>
        </ul>

        <!-- Card Container with Tab Contents -->
        <div class="card card-outline card-info rounded-0 border-top-0 mb-4">
          <div class="card-body p-3">

            <!-- Loading Spinner -->
            <div id="loading-state" class="text-center text-primary py-5" style="display: none;">
              <div class="spinner-border spinner-border-lg mr-2" role="status"></div>
              <p class="mt-2 font-weight-bold">Loading students for selected batch...</p>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="text-center text-muted py-5" style="display: none;">
              <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
              <h5>No students found for this batch.</h5>
            </div>

            <!-- Tab Content Panels -->
            <div class="tab-content" id="exam-tabs-content">

              <!-- WRITTEN EXAM TAB -->
              <div class="tab-pane fade show active" id="written-tab" role="tabpanel" aria-labelledby="written-tab-link">
                <div class="alert alert-primary border-0 shadow-none mb-3">
                  <i class="fas fa-info-circle mr-2"></i> Enter the written exam marks and mark students as passed (if applicable).
                </div>

                <!-- Passing Marks Section for Written Tab -->
                <div class="row mb-3 align-items-center">
                  <div class="col-md-6">
                    <div class="form-group mb-0">
                      <label for="passingWrittenMarks" class="font-weight-bold mb-1">
                        Passing Marks for Written Exam <span class="text-danger">*</span>
                      </label>
                      <input type="number" step="0.01" min="0" class="form-control" id="passingWrittenMarks"
                             placeholder="e.g. 50.00" oninput="onPassingMarksChange('written', this.value);">
                      <div class="invalid-feedback err-passing-written"></div>
                    </div>
                  </div>
                  <div class="col-md-6 text-md-right font-weight-bold text-primary header-max-written mt-2 mt-md-0">
                    Max Marks: <span class="max-written-val">--</span>
                  </div>
                </div>

                <div class="table-responsive table-responsive-sticky">
                  <table class="table table-striped table-hover mb-0">
                    <thead>
                      <tr>
                        <th scope="col" style="width: 8%;">Sr. No.</th>
                        <th scope="col" style="width: 18%;">Application No.</th>
                        <th scope="col" style="width: 18%;">Enrollment No.</th>
                        <th scope="col" style="width: 26%;">Student Name</th>
                        <th scope="col" style="width: 18%;">Marks (<span class="max-written-header">Max: --</span>)</th>
                        <th scope="col" style="width: 12%;">Passed</th>
                      </tr>
                    </thead>
                    <tbody id="written-table-body">
                      <!-- Rendered dynamically -->
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- PRESENTATION TAB -->
              <div class="tab-pane fade" id="presentation-tab" role="tabpanel" aria-labelledby="presentation-tab-link">
                <div class="alert alert-primary border-0 shadow-none mb-3">
                  <i class="fas fa-info-circle mr-2"></i> Enter the presentation marks and mark students as passed (if applicable).
                </div>

                <!-- Passing Marks Section for Presentation Tab -->
                <div class="row mb-3 align-items-center">
                  <div class="col-md-6">
                    <div class="form-group mb-0">
                      <label for="passingPresentationMarks" class="font-weight-bold mb-1">
                        Passing Marks for Presentation Exam <span class="text-danger">*</span>
                      </label>
                      <input type="number" step="0.01" min="0" class="form-control" id="passingPresentationMarks"
                             placeholder="e.g. 50.00" oninput="onPassingMarksChange('presentation', this.value);">
                      <div class="invalid-feedback err-passing-pres"></div>
                    </div>
                  </div>
                  <div class="col-md-6 text-md-right font-weight-bold text-primary header-max-presentation mt-2 mt-md-0">
                    Max Marks: <span class="max-pres-val">--</span>
                  </div>
                </div>

                <div class="table-responsive table-responsive-sticky">
                  <table class="table table-striped table-hover mb-0">
                    <thead>
                      <tr>
                        <th scope="col" style="width: 8%;">Sr. No.</th>
                        <th scope="col" style="width: 18%;">Application No.</th>
                        <th scope="col" style="width: 18%;">Enrollment No.</th>
                        <th scope="col" style="width: 26%;">Student Name</th>
                        <th scope="col" style="width: 18%;">Marks (<span class="max-pres-header">Max: --</span>)</th>
                        <th scope="col" style="width: 12%;">Passed</th>
                      </tr>
                    </thead>
                    <tbody id="presentation-table-body">
                      <!-- Rendered dynamically -->
                    </tbody>
                  </table>
                </div>
              </div>

              <?php require_once pathOf('/admission-exam-results/presentation-ppt.php'); ?>

            </div>

          </div>

          <!-- STICKY FOOTER -->
          <div class="card-footer sticky-card-footer d-flex justify-content-between align-items-center py-3">
            <div>
              <span id="total-students-count" class="font-weight-bold text-dark" style="font-size: 1.05rem;">0 Students</span>
            </div>
            <div>
              <a href="<?= urlOf('/admission-exam-results') ?>" class="btn btn-secondary mr-2 px-4">Cancel</a>
              <button type="button" class="btn btn-success btn-submit px-4" id="save-batch-btn" onclick="saveActiveTabMarks();">
                <span class="btn-submit-spinner spinner-border spinner-border-sm mr-2" style="display:none;" role="status"></span>
                <span class="btn-submit-text"><i class="fas fa-save mr-2"></i><span id="save-btn-label">Save Written Exam Marks</span></span>
              </button>
            </div>
          </div>

        </div>
      </div>

    </section>
  </div>
</div>

<!-- Remove PPT Confirmation Modal -->
<div class="modal fade" id="removePptModal" tabindex="-1" role="dialog" aria-labelledby="removePptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold" id="removePptModalLabel"><i class="fas fa-exclamation-triangle mr-2"></i>Confirm Removal</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body py-4">
        <p class="mb-0 font-weight-bold text-dark">Are you sure you want to remove this Presentation PPT?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmRemovePptBtn" onclick="confirmDeletePpt();">
          <span class="spinner-border spinner-border-sm mr-2" id="remove-ppt-spinner" style="display:none;" role="status"></span>
          Remove
        </button>
      </div>
    </div>
  </div>
</div>

<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/admission-exam-results/js/exam-results.js') ?>"></script>
<script src="<?= urlOf('/admission-exam-results/js/presentation-ppt.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
