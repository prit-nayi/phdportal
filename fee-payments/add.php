<?php

$title = "Add Fee Payment";
Authorize::forRolesOrRedirect([Role::ADMIN]);

$db = Database::getInstance();

// Fetch active students for the student selection dropdown
$students = $db->getAll(
  "SELECT s.`id`, s.`StudentName`, s.`Surname`, s.`ApplicationNo`, s.`EmailStudent`,
          b.`name` AS batchName, b.`enrollmentYear`
   FROM `phdStudents` s
   LEFT JOIN `phdBatches` b ON s.`batchId` = b.`id`
   WHERE s.`isActive` = 1 AND s.`isCancelled` = 0 AND s.`isTransferred` = 0
   ORDER BY s.`StudentName` ASC, s.`Surname` ASC"
);

// Map existing fee payments by studentId to track paid fee types
$rawPayments = $db->getAll("SELECT `studentId`, `feeType` FROM `feePayments`");
$paidFeeTypesMap = [];
foreach ($rawPayments as $p) {
  $sId = (int) $p->studentId;
  if (!isset($paidFeeTypesMap[$sId])) {
    $paidFeeTypesMap[$sId] = [];
  }
  $paidFeeTypesMap[$sId][] = $p->feeType;
}

foreach ($students as $s) {
  $s->paidFeeTypes = $paidFeeTypesMap[(int) $s->id] ?? [];
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
            <h1>Add Fee Payment</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/fee-payments') ?>">Fee Payments</a></li>
              <li class="breadcrumb-item active">Add Payment</li>
            </ol>
          </div>
        </div>
        <div class="row">&nbsp;</div>
      </div>
    </section>

    <section class="content">
      <!-- Form Alert Placeholder -->
      <div id="form-alert" style="display:none;"></div>

      <div class="row">
        <div class="col">
          <form id="payment-form" onsubmit="addPayment(event);" enctype="multipart/form-data" novalidate>
            <div class="card card-outline card-info">
              <div class="card-header">
                <h3 class="card-title">Fee Payment Details (Cheque Payment)</h3>
              </div>
              <div class="card-body">

                <!-- Student Selection & Fee Type -->
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="studentId">Student <span class="text-danger">*</span></label>
                      <div class="searchable-select-wrapper" id="student-select-wrapper">
                        <!-- Hidden input holding the selected student ID for form submission -->
                        <input type="hidden" id="studentId" name="studentId" value="" required>

                        <!-- Dropdown Trigger Button -->
                        <div class="searchable-select-trigger" id="studentId-trigger" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false">
                          <span class="selected-text text-truncate" id="studentId-display-text">-- Select Student --</span>
                          <i class="fas fa-chevron-down text-muted ml-2"></i>
                        </div>

                        <!-- Dropdown Menu -->
                        <div class="searchable-select-menu" id="studentId-menu" style="display: none;">
                          <div class="searchable-select-search-container">
                            <div class="input-group input-group-sm">
                              <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                              </div>
                              <input type="text" class="form-control border-left-0 student-search-input" id="studentId-search" placeholder="Search student..." autocomplete="off">
                              <div class="input-group-append" id="studentId-search-clear" style="display: none;">
                                <button class="btn btn-outline-secondary" type="button" id="btn-clear-search"><i class="fas fa-times"></i></button>
                              </div>
                            </div>
                          </div>
                          <div class="searchable-select-list" id="studentId-list" role="listbox">
                            <!-- Populated dynamically via JS -->
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="feeType">Fee Type <span class="text-danger">*</span></label>
                      <select class="form-control" id="feeType" name="feeType" required>
                        <option value="">-- Select Fee Type --</option>
                        <option value="Regular">Regular</option>
                        <option value="Re-registration">Re-registration</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Payment Mode (Disabled / Read-only Cheque option) & Paid On Date -->
                <div class="row mt-2">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="paymentModeDisplay">Payment Mode</label>
                      <input
                        type="text"
                        class="form-control"
                        id="paymentModeDisplay"
                        value="Cheque"
                        disabled>
                      <!-- Hidden input to submit 'Bank' as expected by the ENUM in DB -->
                      <input type="hidden" id="paymentMode" name="paymentMode" value="Bank">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="paidOnDate">Paid On Date <span class="text-danger">*</span></label>
                      <input
                        type="date"
                        class="form-control"
                        id="paidOnDate"
                        name="paidOnDate"
                        value="<?= date('Y-m-d') ?>"
                        max="<?= date('Y-m-d') ?>"
                        required>
                    </div>
                  </div>
                </div>

                <!-- Cheque Payment Details Card -->
                <div class="card border border-info p-3 mt-3 bg-light">
                  <h5 class="text-info mb-3"><i class="fas fa-university mr-2"></i>Cheque Details</h5>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="chequeNumber">Cheque Number <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          class="form-control"
                          id="chequeNumber"
                          name="chequeNumber"
                          placeholder="e.g. CHQ-100293"
                          required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="bankName">Bank Name <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          class="form-control"
                          id="bankName"
                          name="bankName"
                          placeholder="e.g. State Bank of India"
                          required>
                      </div>
                    </div>
                  </div>
                  <div class="row mt-2">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="ifsc">IFSC Code <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          class="form-control"
                          id="ifsc"
                          name="ifsc"
                          placeholder="e.g. SBIN0001234"
                          required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="branchName">Branch Name <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          class="form-control"
                          id="branchName"
                          name="branchName"
                          placeholder="e.g. Main Campus Branch"
                          required>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Remarks & Receipt File Upload -->
                <div class="row mt-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="remarks">Remarks</label>
                      <textarea
                        class="form-control"
                        id="remarks"
                        name="remarks"
                        rows="3"
                        placeholder="Optional comments or notes about this payment..."></textarea>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="receipt">Receipt File Upload</label>
                      <input
                        type="file"
                        class="form-control-file"
                        id="receipt"
                        name="receipt"
                        accept=".pdf,.jpg,.jpeg,.png">
                      <small class="form-text text-muted">Allowed formats: PDF, JPG, JPEG, PNG.</small>
                    </div>
                  </div>
                </div>

              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-success btn-submit" id="btn-submit">
                  <span class="btn-submit-text">Submit</span>
                  <span class="btn-submit-text-saved" style="display: none">Saved!</span>
                  <div class="btn-submit-spinner spinner-border spinner-border-sm" role="status" style="display: none">
                    <span class="sr-only">Saving...</span>
                  </div>
                </button>
                <a href="<?= urlOf('/fee-payments') ?>" class="btn btn-secondary ml-2">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>
</div>

<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script>
  window.__STUDENTS__ = <?= json_encode($students, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  window.__SELECTED_STUDENT_ID__ = null;
</script>
<script src="<?= urlOf('/fee-payments/js/fee-payments.js') ?>?v=<?= time() ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
