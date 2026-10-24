<?php

$title = "PhD Guide Management";

Authorize::forRolesOrRedirect([Role::ADMIN]);
$db = Database::getInstance();

// ── Ensure database table exists ──────────────────────────────────────────────
$db->execute("CREATE TABLE IF NOT EXISTS `phdGuides` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `designation` VARCHAR(255) NOT NULL,
    `isActive` BOOLEAN NOT NULL DEFAULT TRUE,
    `maximumNumberOfScholars` INT NOT NULL DEFAULT 0,
    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
);");

// ── Pagination config ─────────────────────────────────────────────────────────
$perPage     = 10;
$currentPage = isset($_GET['page']) && ctype_digit($_GET['page']) && (int) $_GET['page'] > 0
  ? (int) $_GET['page']
  : 1;
$offset = ($currentPage - 1) * $perPage;

// ── Filters ───────────────────────────────────────────────────────────────────
$searchTerm   = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'all';

$whereClauses = [];
$queryParams  = [];

if ($searchTerm !== '') {
  $whereClauses[] = "(`name` LIKE ? OR `designation` LIKE ?)";
  $like = '%' . $searchTerm . '%';
  $queryParams[] = $like;
  $queryParams[] = $like;
}

if ($statusFilter === 'active') {
  $whereClauses[] = "`isActive` = 1";
} elseif ($statusFilter === 'inactive') {
  $whereClauses[] = "`isActive` = 0";
}

$whereSql = '';
if (count($whereClauses) > 0) {
  $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// ── Count Query ───────────────────────────────────────────────────────────────
$countQuery  = "SELECT COUNT(*) AS `total` FROM `phdGuides` $whereSql";
$totalRecord = $db->get($countQuery, $queryParams);
$totalRows   = $totalRecord ? (int) $totalRecord->total : 0;

// ── Data Query ────────────────────────────────────────────────────────────────
$dataQuery = "SELECT * FROM `phdGuides`
              $whereSql
              ORDER BY `name` ASC
              LIMIT $perPage OFFSET $offset";

$allRecords = $db->getAll($dataQuery, $queryParams);
$totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;

// ── Helper: build page URL preserving filters ─────────────────────────────────
function buildGuidePageUrl(int $page, string $search, string $status): string
{
  $params = ['page' => $page];
  if ($search !== '') {
    $params['search'] = $search;
  }
  if ($status !== 'all') {
    $params['status'] = $status;
  }
  return '?' . http_build_query($params);
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
            <h1>PhD Guide Management</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item active">Guides</li>
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
          <a role="button" class="btn btn-success" href="<?= urlOf('/guides/add') ?>">
            <i class="fas fa-plus mr-1"></i> Add New Guide
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
                  <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Only</option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
                  </select>
                </div>
                <div class="col-lg-4 col-md-5 col-sm-6 mb-2">
                  <div class="input-group">
                    <input
                      type="search"
                      class="form-control"
                      name="search"
                      placeholder="Search by name or designation..."
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
                        <th scope="col" class="item-list-number" style="width: 6%">#</th>
                        <th scope="col" class="item-list-title">Guide Name</th>
                        <th scope="col" class="item-list-title">Designation</th>
                        <th scope="col" class="item-list-title text-center">Maximum Scholars</th>
                        <th scope="col" class="item-list-title text-center">Status</th>
                        <th scope="col" class="item-list-actions text-center" style="width: 18%">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($allRecords) === 0): ?>
                        <tr>
                          <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>
                            No guide records found.
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
                              <?= htmlspecialchars($row->designation, ENT_QUOTES) ?>
                            </td>
                            <td class="align-middle text-center">
                              <span class="badge badge-light border px-2 py-1" style="font-size: 0.9em;">
                                <?= (int) $row->maximumNumberOfScholars ?>
                              </span>
                            </td>
                            <td class="align-middle text-center">
                              <?php if ($row->isActive): ?>
                                <span class="badge badge-success px-2 py-1">Active</span>
                              <?php else: ?>
                                <span class="badge badge-secondary px-2 py-1">Inactive</span>
                              <?php endif; ?>
                            </td>
                            <td class="item-list-actions align-middle text-center">
                              <div class="btn-group" role="group" aria-label="Actions">
                                <a role="button"
                                   class="btn btn-sm btn-primary"
                                   href="<?= urlOf("/guides/edit?id={$row->id}") ?>"
                                   data-toggle="tooltip"
                                   data-placement="bottom"
                                   title="Edit Guide">
                                  <i class="far fa-edit"></i> Edit
                                </a>
                                <button type="button"
                                   class="btn btn-sm <?= $row->isActive ? 'btn-warning' : 'btn-success' ?> btn-status-toggle"
                                   onclick="changeStatus(event, this, <?= (int) $row->id ?>)"
                                   data-status="<?= $row->isActive ? 'active' : 'inactive' ?>"
                                   data-toggle="tooltip"
                                   data-placement="bottom"
                                   title="<?= $row->isActive ? 'Deactivate Guide' : 'Activate Guide' ?>">
                                  <span class="btn-submit-text">
                                    <i class="fas <?= $row->isActive ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                    <?= $row->isActive ? 'Deactivate' : 'Activate' ?>
                                  </span>
                                  <span class="btn-submit-spinner spinner-border spinner-border-sm" role="status" style="display:none;"></span>
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
                  <nav aria-label="Guides pagination" class="mt-3">
                    <ul class="pagination justify-content-center flex-wrap">

                      <!-- Previous -->
                      <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= buildGuidePageUrl($currentPage - 1, $searchTerm, $statusFilter) ?>"
                           aria-label="Previous">
                          <span aria-hidden="true">&laquo;</span>
                        </a>
                      </li>

                      <!-- Page numbers -->
                      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                          <a class="page-link" href="<?= buildGuidePageUrl($p, $searchTerm, $statusFilter) ?>"><?= $p ?></a>
                        </li>
                      <?php endfor; ?>

                      <!-- Next -->
                      <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= buildGuidePageUrl($currentPage + 1, $searchTerm, $statusFilter) ?>"
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
<script src="<?= urlOf('/guides/js/guides.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
