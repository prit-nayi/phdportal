<?php

$title = "Presentation Schedule";

Authorize::forRolesOrRedirect([Role::ADMIN]);
$db = Database::getInstance();

// ── Ensure database table exists ──────────────────────────────────────────────
$db->execute("CREATE TABLE IF NOT EXISTS `guideAllocationPresentations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `batchId` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `scheduledOn` DATE NOT NULL,
    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
);");

// ── Fetch all batches for filter dropdown ──────────────────────────────────────
$batches = $db->getAll("SELECT * FROM `phdBatches` ORDER BY `enrollmentYear` DESC, `name` ASC");

// ── Pagination config ─────────────────────────────────────────────────────────
$perPage     = 10;
$currentPage = isset($_GET['page']) && ctype_digit($_GET['page']) && (int) $_GET['page'] > 0
  ? (int) $_GET['page']
  : 1;
$offset = ($currentPage - 1) * $perPage;

// ── Filters ───────────────────────────────────────────────────────────────────
$searchTerm  = isset($_GET['search']) ? trim($_GET['search']) : '';
$batchFilter = isset($_GET['batchId']) && ctype_digit($_GET['batchId']) ? (int) $_GET['batchId'] : 0;

$whereClauses = [];
$queryParams  = [];

if ($searchTerm !== '') {
  $whereClauses[] = "p.`name` LIKE ?";
  $queryParams[]  = '%' . $searchTerm . '%';
}

if ($batchFilter > 0) {
  $whereClauses[] = "p.`batchId` = ?";
  $queryParams[]  = $batchFilter;
}

$whereSql = '';
if (count($whereClauses) > 0) {
  $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// ── Count Query ───────────────────────────────────────────────────────────────
$countQuery  = "SELECT COUNT(*) AS `total`
                FROM `guideAllocationPresentations` p
                JOIN `phdBatches` b ON p.`batchId` = b.`id`
                $whereSql";
$totalRecord = $db->get($countQuery, $queryParams);
$totalRows   = $totalRecord ? (int) $totalRecord->total : 0;

// ── Data Query ────────────────────────────────────────────────────────────────
$dataQuery = "SELECT p.*, b.`name` AS `batchName`, b.`enrollmentYear`
              FROM `guideAllocationPresentations` p
              JOIN `phdBatches` b ON p.`batchId` = b.`id`
              $whereSql
              ORDER BY p.`scheduledOn` DESC, p.`name` ASC
              LIMIT $perPage OFFSET $offset";

$allRecords = $db->getAll($dataQuery, $queryParams);
$totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;

// ── Helper: build page URL preserving filters ─────────────────────────────────
function buildPresentationPageUrl(int $page, string $search, int $batchId): string
{
  $params = ['page' => $page];
  if ($search !== '') {
    $params['search'] = $search;
  }
  if ($batchId > 0) {
    $params['batchId'] = $batchId;
  }
  return '?' . http_build_query($params);
}

// ── Helper: display date format (e.g. 20 September 2026) ──────────────────────
function formatScheduledDate(string $dateStr): string
{
  return date('d F Y', strtotime($dateStr));
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
            <h1>Presentation Schedule</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item active">Presentation Schedule</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">

      <!-- Flash alert placeholder -->
      <div id="flash-alert" style="display:none;"></div>

      <div class="row">
        <div class="col text-right">
          <a role="button" class="btn btn-success" href="<?= urlOf('/guide-allocation-presentations/add') ?>">
            <i class="fas fa-plus mr-1"></i> Add New Presentation Schedule
          </a>
        </div>
      </div>
      <div class="row">&nbsp;</div>
      <div class="card card-outline card-info">
        <div class="card-body">
          <div class="col col-md-12">

            <!-- Search & Filter Form -->
            <form action="" method="get" class="mb-4">
              <div class="form-row justify-content-end">
                <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                  <select name="batchId" class="form-control" onchange="this.form.submit()">
                    <option value="0">All Batches</option>
                    <?php foreach ($batches as $batch): ?>
                      <option value="<?= (int) $batch->id ?>" <?= $batchFilter === (int) $batch->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($batch->name, ENT_QUOTES) ?> (<?= (int) $batch->enrollmentYear ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-lg-4 col-md-5 col-sm-6 mb-2">
                  <div class="input-group">
                    <input
                      type="search"
                      class="form-control"
                      name="search"
                      placeholder="Search by presentation name..."
                      value="<?= htmlspecialchars($searchTerm, ENT_QUOTES) ?>">
                    <div class="input-group-append">
                      <button type="submit" class="btn btn-info">
                        <i class="fa fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </form>

            <!-- Table -->
            <div class="row">
              <div class="col">
                <div class="table-responsive">
                  <table class="table table-striped item-list">
                    <thead>
                      <tr>
                        <th scope="col" class="item-list-number" style="width: 5%">#</th>
                        <th scope="col" class="item-list-title">Presentation Name</th>
                        <th scope="col" class="item-list-title">Batch</th>
                        <th scope="col" class="item-list-title text-center">Scheduled Date</th>
                        <th scope="col" class="item-list-actions text-center" style="width: 16%">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($allRecords) === 0): ?>
                        <tr>
                          <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                            No presentation schedule records found.
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($allRecords as $i => $row): ?>
                          <tr>
                            <th scope="row" class="item-list-number align-middle">
                              <?= $offset + $i + 1 ?>
                            </th>
                            <td class="item-list-name align-middle font-weight-bold">
                              <?= htmlspecialchars($row->name, ENT_QUOTES) ?>
                            </td>
                            <td class="item-list-name align-middle">
                              <span class="badge badge-light border px-2 py-1" style="font-size: 0.9em;">
                                <i class="fas fa-layer-group text-info mr-1"></i>
                                <?= htmlspecialchars($row->batchName, ENT_QUOTES) ?> (<?= (int) $row->enrollmentYear ?>)
                              </span>
                            </td>
                            <td class="align-middle text-center">
                              <span class="badge badge-info px-2 py-1" style="font-size: 0.9em;">
                                <i class="far fa-calendar-alt mr-1"></i>
                                <?= formatScheduledDate($row->scheduledOn) ?>
                              </span>
                            </td>
                            <td class="item-list-actions align-middle text-center">
                              <div class="btn-group" role="group" aria-label="Actions">
                                <a role="button"
                                   class="btn btn-sm btn-primary"
                                   href="<?= urlOf("/guide-allocation-presentations/edit?id={$row->id}") ?>"
                                   data-toggle="tooltip"
                                   data-placement="bottom"
                                   title="Edit Presentation Schedule">
                                  <i class="far fa-edit"></i> Edit
                                </a>
                                <button type="button"
                                   class="btn btn-sm btn-danger"
                                   onclick="showDeleteConfirmation(<?= (int) $row->id ?>)"
                                   data-toggle="tooltip"
                                   data-placement="bottom"
                                   title="Delete Presentation Schedule">
                                  <i class="far fa-trash-alt"></i>
                                </button>
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
                  <nav aria-label="Presentations pagination" class="mt-3">
                    <ul class="pagination justify-content-center flex-wrap">

                      <!-- Previous -->
                      <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= buildPresentationPageUrl($currentPage - 1, $searchTerm, $batchFilter) ?>"
                           aria-label="Previous">
                          <span aria-hidden="true">&laquo;</span>
                        </a>
                      </li>

                      <!-- Page numbers -->
                      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                          <a class="page-link" href="<?= buildPresentationPageUrl($p, $searchTerm, $batchFilter) ?>"><?= $p ?></a>
                        </li>
                      <?php endfor; ?>

                      <!-- Next -->
                      <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= buildPresentationPageUrl($currentPage + 1, $searchTerm, $batchFilter) ?>"
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
                        <h5 class="modal-title" id="modal-delete-title">Confirm Delete Presentation Schedule</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        Are you sure you want to delete this presentation schedule? This action cannot be undone.
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
<script src="<?= urlOf('/guide-allocation-presentations/js/guide-allocation-presentations.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
