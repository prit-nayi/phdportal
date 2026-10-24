<?php

$title = "Fee Payments";
Authorize::forRolesOrRedirect([Role::ADMIN]);

$db = Database::getInstance();

// ── Pagination Config ────────────────────────────────────────────────────────
$perPage = 20;
$currentPage = isset($_GET['page']) && ctype_digit($_GET['page']) && (int) $_GET['page'] > 0
  ? (int) $_GET['page']
  : 1;
$offset = ($currentPage - 1) * $perPage;

// ── Search Parameters ────────────────────────────────────────────────────────
$searchStudent = isset($_GET['search_student']) ? trim($_GET['search_student']) : '';
$searchFeeType = isset($_GET['search_fee_type']) ? trim($_GET['search_fee_type']) : '';
$searchPaidDate = isset($_GET['search_paid_date']) ? trim($_GET['search_paid_date']) : '';

// ── Sorting Parameters ───────────────────────────────────────────────────────
$allowedSorts = [
  'student' => 's.StudentName',
  'feetype' => 'p.feeType',
  'paiddate' => 'p.paidOnDate',
  'created' => 'p.createdAt'
];

$sortBy = isset($_GET['sort']) && array_key_exists($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'created';
$sortColumn = $allowedSorts[$sortBy];

$sortDir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
$toggleDir = $sortDir === 'ASC' ? 'DESC' : 'ASC';

// ── Build Query ──────────────────────────────────────────────────────────────
$whereClauses = [];
$params = [];

if ($searchStudent !== '') {
  $whereClauses[] = "(s.StudentName LIKE ? OR s.Surname LIKE ? OR s.ApplicationNo LIKE ? OR p.chequeNumber LIKE ?)";
  $params[] = '%' . $searchStudent . '%';
  $params[] = '%' . $searchStudent . '%';
  $params[] = '%' . $searchStudent . '%';
  $params[] = '%' . $searchStudent . '%';
}

if ($searchFeeType !== '') {
  $whereClauses[] = "p.feeType = ?";
  $params[] = $searchFeeType;
}

if ($searchPaidDate !== '') {
  $whereClauses[] = "p.paidOnDate = ?";
  $params[] = $searchPaidDate;
}

$whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : '';

// Total Count
$countQuery = "SELECT COUNT(*) AS `total` 
               FROM `feePayments` p 
               JOIN `phdStudents` s ON p.studentId = s.id 
               $whereSql";
$countRecord = $db->get($countQuery, count($params) > 0 ? $params : null);
$totalRows = $countRecord ? (int) $countRecord->total : 0;
$totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;

// Fetch Paginated & Sorted Records
$selectQuery = "SELECT p.*, s.StudentName, s.Surname, s.ApplicationNo 
                FROM `feePayments` p 
                JOIN `phdStudents` s ON p.studentId = s.id 
                $whereSql 
                ORDER BY $sortColumn $sortDir 
                LIMIT $perPage OFFSET $offset";
$allRecords = $db->getAll($selectQuery, count($params) > 0 ? $params : null);

// Preserve Search and Sort Parameters in Pagination Links
function buildPageUrl(int $page, string $ss, string $sft, string $spd, string $sort, string $dir): string
{
  $params = ['page' => $page];
  if ($ss !== '') $params['search_student'] = $ss;
  if ($sft !== '') $params['search_fee_type'] = $sft;
  if ($spd !== '') $params['search_paid_date'] = $spd;
  $params['sort'] = $sort;
  $params['dir'] = $dir;

  return '?' . http_build_query($params);
}

function displayDate(?string $dateStr): string
{
  if (!$dateStr) return '—';
  return date('d M Y', strtotime($dateStr));
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
            <h1>Fee Payments</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item active">Fee Payments</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <!-- Flash Alert Placeholder -->
      <div id="flash-alert" style="display:none;"></div>

      <div class="row">
        <div class="col text-right">
          <a role="button" class="btn btn-success" href="<?= urlOf('/fee-payments/add') ?>">
            <i class="fas fa-plus mr-1"></i> Add New Payment
          </a>
        </div>
      </div>
      <div class="row">&nbsp;</div>

      <div class="card card-outline card-info">
        <div class="card-body">
          <div class="col col-md-12">

            <!-- Search Filters Form -->
            <div class="row mb-4">
              <div class="col-12">
                <form action="" method="get" class="form-inline d-flex flex-wrap align-items-end" style="gap: 0.5rem;">
                  <input
                    type="search"
                    class="form-control"
                    name="search_student"
                    placeholder="Student name, App No, or Cheque No..."
                    autofocus
                    value="<?= htmlspecialchars($searchStudent, ENT_QUOTES) ?>">
                  <select name="search_fee_type" class="form-control">
                    <option value="">-- Fee Type --</option>
                    <option value="Regular" <?= $searchFeeType === 'Regular' ? 'selected' : '' ?>>Regular</option>
                    <option value="Re-registration" <?= $searchFeeType === 'Re-registration' ? 'selected' : '' ?>>Re-registration</option>
                  </select>
                  <input
                    type="date"
                    class="form-control"
                    name="search_paid_date"
                    title="Paid Date"
                    value="<?= htmlspecialchars($searchPaidDate, ENT_QUOTES) ?>">
                  <button type="submit" class="btn btn-info">
                    <i class="fa fa-search"></i>
                  </button>
                  <?php if ($searchStudent !== '' || $searchFeeType !== '' || $searchPaidDate !== ''): ?>
                    <a href="<?= urlOf('/fee-payments') ?>" class="btn btn-secondary" title="Reset Filters">
                      <i class="fas fa-undo"></i>
                    </a>
                  <?php endif; ?>
                </form>
              </div>
            </div>

            <!-- Table -->
            <div class="row">
              <div class="col">
                <div class="table-responsive">
                  <table class="table table-striped item-list">
                    <thead>
                      <tr>
                        <th scope="col" class="item-list-number" style="width: 5%">#</th>
                        <th scope="col" class="item-list-title">
                          <a href="<?= buildPageUrl(1, $searchStudent, $searchFeeType, $searchPaidDate, 'student', $sortBy === 'student' ? $toggleDir : 'ASC') ?>" class="text-dark">
                            Student Name
                            <?php if ($sortBy === 'student'): ?>
                              <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                            <?php else: ?>
                              <i class="fas fa-sort text-muted ml-1"></i>
                            <?php endif; ?>
                          </a>
                        </th>
                        <th scope="col" class="item-list-title">Application No</th>
                        <th scope="col" class="item-list-title">
                          <a href="<?= buildPageUrl(1, $searchStudent, $searchFeeType, $searchPaidDate, 'feetype', $sortBy === 'feetype' ? $toggleDir : 'ASC') ?>" class="text-dark">
                            Fee Type
                            <?php if ($sortBy === 'feetype'): ?>
                              <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                            <?php else: ?>
                              <i class="fas fa-sort text-muted ml-1"></i>
                            <?php endif; ?>
                          </a>
                        </th>
                        <th scope="col" class="item-list-title">Payment Mode</th>
                        <th scope="col" class="item-list-title">Cheque Number</th>
                        <th scope="col" class="item-list-title">
                          <a href="<?= buildPageUrl(1, $searchStudent, $searchFeeType, $searchPaidDate, 'paiddate', $sortBy === 'paiddate' ? $toggleDir : 'DESC') ?>" class="text-dark">
                            Paid Date
                            <?php if ($sortBy === 'paiddate'): ?>
                              <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                            <?php else: ?>
                              <i class="fas fa-sort text-muted ml-1"></i>
                            <?php endif; ?>
                          </a>
                        </th>
                        <th scope="col" class="item-list-title">Receipt</th>
                        <th scope="col" class="item-list-actions" style="width: 14%">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($allRecords) === 0): ?>
                        <tr>
                          <td colspan="9" class="text-center">No fee payment records found</td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($allRecords as $i => $row): ?>
                          <tr>
                            <th scope="row" class="item-list-number">
                              <?= $offset + $i + 1 ?>
                            </th>
                            <td class="item-list-name">
                              <strong><?= htmlspecialchars($row->StudentName . ' ' . $row->Surname, ENT_QUOTES) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($row->ApplicationNo ?? '—', ENT_QUOTES) ?></td>
                            <td>
                              <span class="badge badge-<?= $row->feeType === 'Regular' ? 'primary' : 'info' ?>">
                                <?= htmlspecialchars($row->feeType, ENT_QUOTES) ?>
                              </span>
                            </td>
                            <td>
                              <span class="badge badge-warning">
                                <i class="fas fa-university mr-1"></i> Cheque
                              </span>
                            </td>
                            <td><strong><?= htmlspecialchars($row->chequeNumber ?? '—', ENT_QUOTES) ?></strong></td>
                            <td><?= displayDate($row->paidOnDate) ?></td>
                            <td>
                              <?php if (!empty($row->receiptFileName)): ?>
                                <a href="<?= urlOf("/@/uploads/fee-payments/{$row->receiptFileName}") ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                  <i class="fas fa-file-download mr-1"></i> View Receipt
                                </a>
                              <?php else: ?>
                                <span class="text-muted">—</span>
                              <?php endif; ?>
                            </td>
                            <td class="item-list-actions">
                              <div class="btn-group d-flex" role="group" aria-label="Actions">
                                <a role="button"
                                   class="btn btn-info btn-sm"
                                   href="<?= urlOf("/fee-payments/view?id={$row->id}") ?>"
                                   data-toggle="tooltip"
                                   data-placement="bottom"
                                   title="View">
                                  <i class="far fa-eye"></i>
                                </a>
                                <a role="button"
                                   class="btn btn-primary btn-sm"
                                   href="<?= urlOf("/fee-payments/edit?id={$row->id}") ?>"
                                   data-toggle="tooltip"
                                   data-placement="bottom"
                                   title="Edit">
                                  <i class="far fa-edit"></i>
                                </a>
                                <a role="button"
                                   class="btn btn-danger btn-sm"
                                   onclick="showDeleteConfirmation(<?= (int) $row->id ?>)"
                                   data-toggle="tooltip"
                                   data-placement="bottom"
                                   title="Delete">
                                  <i class="far fa-trash-alt"></i>
                                </a>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                  <nav aria-label="Fee payments pagination" class="mt-3">
                    <ul class="pagination justify-content-center flex-wrap">

                      <!-- Previous -->
                      <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= buildPageUrl($currentPage - 1, $searchStudent, $searchFeeType, $searchPaidDate, $sortBy, $sortDir) ?>"
                           aria-label="Previous">
                          <span aria-hidden="true">&laquo;</span>
                        </a>
                      </li>

                      <!-- Page numbers -->
                      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                          <a class="page-link" href="<?= buildPageUrl($p, $searchStudent, $searchFeeType, $searchPaidDate, $sortBy, $sortDir) ?>"><?= $p ?></a>
                        </li>
                      <?php endfor; ?>

                      <!-- Next -->
                      <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= buildPageUrl($currentPage + 1, $searchStudent, $searchFeeType, $searchPaidDate, $sortBy, $sortDir) ?>"
                           aria-label="Next">
                          <span aria-hidden="true">&raquo;</span>
                        </a>
                      </li>

                    </ul>
                  </nav>
                  <p class="text-center text-muted" style="font-size:0.85em;">
                    Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalRows) ?> of <?= $totalRows ?> record(s)
                  </p>
                <?php endif; ?>

                <!-- Delete confirmation modal -->
                <div class="modal fade" id="modal-delete" tabindex="-1" role="dialog"
                     aria-labelledby="modal-delete-title" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modal-delete-title">Confirm Delete</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        Are you sure you want to delete this fee payment record? The receipt file will also be permanently removed.
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button id="btn-yes" type="button" class="btn btn-danger" onclick="_delete()">
                          <span id="btn-yes-text">Yes, Delete</span>
                          <span id="btn-yes-spinner" class="spinner-border spinner-border-sm" role="status" style="display:none;"></span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- /.row -->
          </div>
        </div>
      </div>
      <br>
    </section>
  </div>
</div>
<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/fee-payments/js/fee-payments.js') ?>?v=<?= time() ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
