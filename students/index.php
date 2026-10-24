<?php

$title = "Students";

Authorize::forRolesOrRedirect([Role::ADMIN]);
$db = Database::getInstance();

// ── Pagination ────────────────────────────────────────────────────────────────
$perPage     = 10;
$currentPage = isset($_GET['page']) && ctype_digit($_GET['page']) && (int) $_GET['page'] > 0
  ? (int) $_GET['page'] : 1;
$offset = ($currentPage - 1) * $perPage;

// ── Search & Batch filter ─────────────────────────────────────────────────────
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$batchFilter = isset($_GET['batch']) && ctype_digit($_GET['batch']) ? (int) $_GET['batch'] : 0;

// Fetch all batches for the filter dropdown
$allBatches = $db->getAll(
  "SELECT `id`, `name`, `enrollmentYear` FROM `phdBatches` ORDER BY `enrollmentYear` DESC, `name` ASC"
);

$baseJoin = "FROM `phdStudents` s
             LEFT JOIN `phdBatches` b ON s.`batchId` = b.`id`";

$selectCols = "s.`id`, s.`ApplicationNo`, s.`StudentName`, s.`Surname`,
               s.`ProgramName`, s.`Status`, s.`isActive`, s.`isCancelled`,
               s.`isTransferred`, s.`createdAt`, s.`updatedAt`,
               b.`name` AS batchName, b.`enrollmentYear`";

// Build dynamic WHERE conditions
$conditions = [];
$params     = [];

if ($batchFilter > 0) {
  $conditions[] = "s.`batchId` = ?";
  $params[]     = $batchFilter;
}

if ($searchTerm !== '') {
  $like = '%' . $searchTerm . '%';
  $conditions[] = "(s.`StudentName` LIKE ? OR s.`Surname` LIKE ?
                    OR s.`ApplicationNo` LIKE ? OR b.`name` LIKE ?)";
  array_push($params, $like, $like, $like, $like);
}

$whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

$totalRecord = $db->get(
  "SELECT COUNT(*) AS `total` $baseJoin $whereClause",
  $params ?: null
);
$totalRows = $totalRecord ? (int) $totalRecord->total : 0;

$allRecords = $db->getAll(
  "SELECT $selectCols $baseJoin $whereClause
   ORDER BY s.`id` DESC
   LIMIT $perPage OFFSET $offset",
  $params ?: null
);

$totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;

// ── Helpers ───────────────────────────────────────────────────────────────────
function buildPageUrl(int $page, string $search, int $batch = 0): string
{
  $params = ['page' => $page];
  if ($search !== '') $params['search'] = $search;
  if ($batch > 0) $params['batch'] = $batch;

  return '?' . http_build_query($params);
}

function readableDate(?string $ts): string
{
  if (!$ts) return '—';
  return date('d M Y, h:i A', strtotime($ts));
}

function studentStatusBadge(object $row): string
{
  if ($row->isCancelled) return '<span class="badge badge-danger">Cancelled</span>';
  if ($row->isTransferred) return '<span class="badge badge-warning">Transferred</span>';
  if (!$row->isActive) return '<span class="badge badge-secondary">Inactive</span>';
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
          <div class="col-sm-6"><h1>Students</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/students') ?>">Home</a></li>
              <li class="breadcrumb-item active">Students</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">

      <div id="flash-alert" style="display:none;"></div>

      <div class="row">
        <div class="col text-right">
          <a role="button" class="btn btn-success" href="<?= urlOf('/students/add') ?>">Add New</a>
        </div>
      </div>
      <div class="row">&nbsp;</div>

      <div class="card card-outline card-info">
        <div class="card-body">
          <div class="col col-md-12">

            <!-- Filters -->
            <div class="row mb-3">
              <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <form action="" method="get" class="d-flex align-items-center" id="batch-filter-form">
                  <?php if ($searchTerm !== ''): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm, ENT_QUOTES) ?>">
                  <?php endif; ?>
                  <label class="mr-2 mb-0 font-weight-bold text-nowrap" for="batch-filter">Batch:</label>
                  <select class="form-control" name="batch" id="batch-filter" onchange="this.form.submit()">
                    <option value="0">All Batches</option>
                    <?php foreach ($allBatches as $b): ?>
                      <option value="<?= (int) $b->id ?>"
                        <?= $batchFilter === (int) $b->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b->name, ENT_QUOTES) ?> (<?= (int) $b->enrollmentYear ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </div>
              <div class="col-12 col-lg-4 offset-lg-4">
                <form action="" method="get" class="input-group">
                  <?php if ($batchFilter > 0): ?>
                    <input type="hidden" name="batch" value="<?= $batchFilter ?>">
                  <?php endif; ?>

                  <input type="search" class="form-control" name="search"
                    placeholder="Search by name, app no, batch..."
                    value="<?= htmlspecialchars($searchTerm, ENT_QUOTES) ?>">
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-info"><i class="fa fa-search"></i></button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Table -->
            <div class="row">
              <div class="col">
                <table class="table table-striped item-list">
                  <thead>
                    <tr>
                      <th style="width:5%">#</th>
                      <th>Application No</th>
                      <th>Student Name</th>
                      <th>Program</th>
                      <th>Batch</th>
                      <th>Status</th>
                      <th>Created</th>
                      <th style="width:12%">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($allRecords) === 0): ?>
                      <tr><td colspan="8" class="text-center">No records found</td></tr>
                    <?php else: ?>
                      <?php foreach ($allRecords as $i => $row): ?>
                        <tr>
                          <th scope="row"><?= $offset + $i + 1 ?></th>
                          <td><?= htmlspecialchars($row->ApplicationNo, ENT_QUOTES) ?></td>
                          <td><?= htmlspecialchars($row->StudentName . ' ' . $row->Surname, ENT_QUOTES) ?></td>
                          <td><?= htmlspecialchars($row->ProgramName, ENT_QUOTES) ?></td>
                          <td><?= htmlspecialchars($row->batchName ?? '—', ENT_QUOTES) ?> (<?= (int)($row->enrollmentYear ?? 0) ?>)</td>
                          <td><?= studentStatusBadge($row) ?></td>
                          <td style="font-size:0.85em;color:#6c757d;"><?= readableDate($row->createdAt) ?></td>
                          <td>
                            <div class="btn-group d-none d-lg-flex" role="group">
                              <a class="btn btn-primary btn-sm"
                                 href="<?= urlOf("/students/edit?id={$row->id}") ?>"
                                 data-toggle="tooltip" title="Edit">
                                <i class="far fa-edit"></i>
                              </a>
                              <a class="btn btn-danger btn-sm"
                                 onclick="showDeleteConfirmation(<?= (int)$row->id ?>)"
                                 data-toggle="tooltip" title="Delete">
                                <i class="far fa-trash-alt"></i>
                              </a>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                  <nav aria-label="Student pagination">
                    <ul class="pagination justify-content-center flex-wrap">
                      <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildPageUrl($currentPage - 1, $searchTerm, $batchFilter) ?>">&laquo;</a>
                      </li>
                      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                          <a class="page-link" href="<?= buildPageUrl($p, $searchTerm, $batchFilter) ?>"><?= $p ?></a>
                        </li>
                      <?php endfor; ?>
                      <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildPageUrl($currentPage + 1, $searchTerm, $batchFilter) ?>">&raquo;</a>

                      </li>
                    </ul>
                  </nav>
                  <p class="text-center text-muted" style="font-size:0.85em;">
                    Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalRows) ?> of <?= $totalRows ?> record(s)
                  </p>
                <?php endif; ?>

                <!-- Delete modal -->
                <div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modal-delete-title" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modal-delete-title">Confirm Delete</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                      </div>
                      <div class="modal-body">Are you sure you want to delete this student record? This action cannot be undone.</div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
                        <button id="btn-yes" type="button" class="btn btn-danger" onclick="_delete()">
                          <span id="btn-yes-text">Yes, Delete</span>
                          <span id="btn-yes-spinner" class="spinner-border spinner-border-sm" role="status" style="display:none;"></span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
      <br>
    </section>
  </div>
</div>
<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/students/js/students.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
