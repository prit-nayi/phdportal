<?php

$title = "View Fee Payment Details";
Authorize::forRolesOrRedirect([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithRedirect('/fee-payments');
}

$id = (int) $_GET['id'];
$db = Database::getInstance();

$record = $db->get(
  "SELECT p.*, 
          s.StudentName, s.Surname, s.ApplicationNo, s.ProgramName, s.EmailStudent, s.MobileStudent,
          b.name AS batchName, b.enrollmentYear
   FROM `feePayments` p
   JOIN `phdStudents` s ON p.studentId = s.id
   LEFT JOIN `phdBatches` b ON s.batchId = b.id
   WHERE p.id = ?",
  [$id]
);

if (!$record) {
  exitWithRedirect('/fee-payments');
}

function formatDate(?string $dt): string
{
  if (!$dt) return '—';
  return date('d M Y', strtotime($dt));
}

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
            <h1>Fee Payment Details</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/fee-payments') ?>">Fee Payments</a></li>
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
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title">
                <i class="fas fa-receipt mr-2"></i>
                Payment Record #<?= (int) $record->id ?> - <?= htmlspecialchars($record->StudentName . ' ' . $record->Surname, ENT_QUOTES) ?>
              </h3>
            </div>
            <div class="card-body">
              
              <!-- Section 1: Student Information -->
              <h5 class="text-primary mb-3"><i class="fas fa-user-graduate mr-2"></i>Student Details</h5>
              <table class="table table-bordered mb-4">
                <tbody>
                  <tr>
                    <th style="width: 30%">Student Name</th>
                    <td><strong><?= htmlspecialchars($record->StudentName . ' ' . $record->Surname, ENT_QUOTES) ?></strong></td>
                  </tr>
                  <tr>
                    <th>Application Number</th>
                    <td><?= htmlspecialchars($record->ApplicationNo ?? '—', ENT_QUOTES) ?></td>
                  </tr>
                  <tr>
                    <th>Program & Batch</th>
                    <td>
                      <?= htmlspecialchars($record->ProgramName ?? 'PhD', ENT_QUOTES) ?>
                      <?php if (!empty($record->batchName)): ?>
                        (<?= htmlspecialchars($record->batchName, ENT_QUOTES) ?> - <?= (int) $record->enrollmentYear ?>)
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th>Email & Mobile</th>
                    <td>
                      <?= htmlspecialchars($record->EmailStudent ?? '—', ENT_QUOTES) ?>
                      <?php if (!empty($record->MobileStudent)): ?>
                        | <i class="fas fa-phone-alt ml-1 mr-1"></i><?= htmlspecialchars($record->MobileStudent, ENT_QUOTES) ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Section 2: Fee Information -->
              <h5 class="text-info mb-3"><i class="fas fa-file-invoice-dollar mr-2"></i>Fee & Payment Mode Details</h5>
              <table class="table table-bordered mb-4">
                <tbody>
                  <tr>
                    <th style="width: 30%">Fee Type</th>
                    <td>
                      <span class="badge badge-<?= $record->feeType === 'Regular' ? 'primary' : 'info' ?> p-2">
                        <?= htmlspecialchars($record->feeType, ENT_QUOTES) ?>
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th>Paid On Date</th>
                    <td><strong><?= formatDate($record->paidOnDate) ?></strong></td>
                  </tr>
                  <tr>
                    <th>Payment Mode</th>
                    <td>
                      <span class="badge badge-warning p-2">
                        <i class="fas fa-university mr-1"></i> Cheque
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th>Remarks</th>
                    <td><?= !empty($record->remarks) ? nl2br(htmlspecialchars($record->remarks, ENT_QUOTES)) : '<span class="text-muted">None</span>' ?></td>
                  </tr>
                </tbody>
              </table>

              <!-- Section 3: Cheque Payment Details -->
              <h5 class="text-warning mb-3"><i class="fas fa-university mr-2"></i>Cheque Details</h5>
              <table class="table table-bordered mb-4">
                <tbody>
                  <tr>
                    <th style="width: 30%">Cheque Number</th>
                    <td><strong><?= htmlspecialchars($record->chequeNumber ?? '—', ENT_QUOTES) ?></strong></td>
                  </tr>
                  <tr>
                    <th>Bank Name</th>
                    <td><?= htmlspecialchars($record->bankName ?? '—', ENT_QUOTES) ?></td>
                  </tr>
                  <tr>
                    <th>IFSC Code</th>
                    <td><?= htmlspecialchars($record->ifsc ?? '—', ENT_QUOTES) ?></td>
                  </tr>
                  <tr>
                    <th>Branch Name</th>
                    <td><?= htmlspecialchars($record->branchName ?? '—', ENT_QUOTES) ?></td>
                  </tr>
                </tbody>
              </table>

              <!-- Section 4: Receipt Document -->
              <h5 class="text-secondary mb-3"><i class="fas fa-paperclip mr-2"></i>Receipt Document</h5>
              <table class="table table-bordered mb-4">
                <tbody>
                  <tr>
                    <th style="width: 30%">Fee Receipt File</th>
                    <td>
                      <?php if (!empty($record->receiptFileName)): ?>
                        <a href="<?= urlOf("/@/uploads/fee-payments/{$record->receiptFileName}") ?>" target="_blank" class="btn btn-sm btn-outline-info">
                          <i class="fas fa-file-download mr-1"></i> Download / View Receipt File
                        </a>
                      <?php else: ?>
                        <span class="text-muted">No receipt file uploaded</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Section 5: Metadata -->
              <h5 class="text-muted mb-3"><i class="fas fa-clock mr-2"></i>Audit Information</h5>
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <th style="width: 30%">Created Date</th>
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
              <a href="<?= urlOf("/fee-payments/edit?id={$record->id}") ?>" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i> Edit Payment
              </a>
              <a href="<?= urlOf('/fee-payments') ?>" class="btn btn-secondary ml-2">
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
