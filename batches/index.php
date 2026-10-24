<?php

$title = "Batches";

Authorize::forRolesOrRedirect([Role::ADMIN]);
$db = Database::getInstance();

// ── Pagination config ─────────────────────────────────────────────────────────
$perPage     = 10;
$currentPage = isset($_GET['page']) && ctype_digit($_GET['page']) && (int) $_GET['page'] > 0
  ? (int) $_GET['page']
  : 1;
$offset = ($currentPage - 1) * $perPage;

// ── Search ────────────────────────────────────────────────────────────────────
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($searchTerm !== '') {
  $like        = '%' . $searchTerm . '%';
  $totalRecord = $db->get(
    "SELECT COUNT(*) AS `total` FROM `phdBatches`
     WHERE `name` LIKE ? OR `enrollmentYear` LIKE ?",
    [$like, $like]
  );
  $totalRows   = $totalRecord ? (int) $totalRecord->total : 0;

  $allRecords = $db->getAll(
    "SELECT * FROM `phdBatches`
     WHERE `name` LIKE ? OR `enrollmentYear` LIKE ?
     ORDER BY `enrollmentYear` DESC, `name` ASC
     LIMIT $perPage OFFSET $offset",
    [$like, $like]
    );
} else {
  $totalRecord = $db->get("SELECT COUNT(*) AS `total` FROM `phdBatches`");
  $totalRows   = $totalRecord ? (int) $totalRecord->total : 0;

  $allRecords = $db->getAll(
    "SELECT * FROM `phdBatches`
     ORDER BY `enrollmentYear` DESC, `name` ASC
     LIMIT $perPage OFFSET $offset"
    );
}

$totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;

// ── Helper: build page URL preserving search ──────────────────────────────────
function buildPageUrl(int $page, string $search): string
{
  $params = ['page' => $page];
  if ($search !== '') {
    $params['search'] = $search;
  }
  return '?' . http_build_query($params);
}

// ── Helper: Indian currency format ────────────────────────────────────────────
function formatIndianCurrency(float $amount): string
{
  // Format with 2 decimals then apply Indian grouping (e.g. 1,50,000.00)
  $formatted = number_format($amount, 2);
  // Re-apply Indian grouping for amounts >= 1000
  $parts     = explode('.', $formatted);
  $integer   = $parts[0];
  $decimal   = $parts[1] ?? '00';

  // Remove existing commas first
  $integer = str_replace(',', '', $integer);
  $len     = strlen($integer);

  if ($len <= 3) {
    return '₹' . $integer . '.' . $decimal;
  }

  // Last 3 digits
  $last3  = substr($integer, -3);
  $remain = substr($integer, 0, $len - 3);

  // Remaining digits grouped by 2
  $grouped = '';
  while (strlen($remain) > 2) {
    $grouped = ',' . substr($remain, -2) . $grouped;
    $remain  = substr($remain, 0, strlen($remain) - 2);
  }
  $grouped = $remain . $grouped;

  return '₹' . $grouped . ',' . $last3 . '.' . $decimal;
}

// ── Helper: readable date ─────────────────────────────────────────────────────
function readableDate(?string $ts): string
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
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Batches</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/batches') ?>">Home</a></li>
              <li class="breadcrumb-item active">Batches</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">

      <!-- Flash alert placeholder (populated by JS after delete) -->
      <div id="flash-alert" style="display:none;"></div>

      <div class="row">
        <div class="col text-right">
          <a role="button" class="btn btn-success" href="<?= urlOf('/batches/add') ?>">Add New</a>
        </div>
      </div>
      <div class="row">&nbsp;</div>
      <div class="card card-outline card-info">
        <div class="card-body">
          <div class="col col-md-12">

            <!-- Search -->
            <div class="row">
              <div class="col col-lg-4 offset-lg-8 text-right mb-4 col-12">
                <form action="" method="get" class="input-group">
                  <input
                    type="search"
                    class="form-control"
                    name="search"
                    placeholder="Search by name or year..."
                    autofocus
                    value="<?= htmlspecialchars($searchTerm, ENT_QUOTES) ?>">
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-info">
                      <i class="fa fa-search"></i>
                    </button>
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
                      <th scope="col" class="item-list-number" style="width: 6%">#</th>
                      <th scope="col" class="item-list-title">Name</th>
                      <th scope="col" class="item-list-title">Enrollment Year</th>
                      <th scope="col" class="item-list-title">Fee Amount</th>
                      <th scope="col" class="item-list-title">Created</th>
                      <th scope="col" class="item-list-title">Last Updated</th>
                      <th scope="col" class="item-list-actions" style="width: 14%">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($allRecords) === 0): ?>
                      <tr>
                        <td colspan="7" class="text-center">No records found</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($allRecords as $i => $row): ?>
                        <tr>
                          <th scope="row" class="item-list-number">
                            <?= $offset + $i + 1 ?>
                          </th>
                          <td class="item-list-name"><?= htmlspecialchars($row->name, ENT_QUOTES) ?></td>
                          <td class="item-list-name"><?= (int) $row->enrollmentYear ?></td>
                          <td class="item-list-name"><?= formatIndianCurrency((float) $row->feeAmount) ?></td>
                          <td class="item-list-name" style="font-size:0.85em;color:#6c757d;">
                            <?= readableDate($row->createdAt) ?>
                          </td>
                          <td class="item-list-name" style="font-size:0.85em;color:#6c757d;">
                            <?= readableDate($row->updatedAt) ?>
                          </td>
                          <td class="item-list-actions">
                            <div class="btn-group d-none d-lg-flex" role="group" aria-label="Actions">
                              <a role="button"
                                 class="btn btn-primary"
                                 href="<?= urlOf("/batches/edit?id={$row->id}") ?>"
                                 data-toggle="tooltip"
                                 data-placement="bottom"
                                 title="Edit">
                                <i class="far fa-edit"></i>
                              </a>
                              <a role="button"
                                 class="btn btn-danger"
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

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                  <nav aria-label="Batch pagination">
                    <ul class="pagination justify-content-center flex-wrap">

                      <!-- Previous -->
                      <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= buildPageUrl($currentPage - 1, $searchTerm) ?>"
                           aria-label="Previous">
                          <span aria-hidden="true">&laquo;</span>
                        </a>
                      </li>

                      <!-- Page numbers -->
                      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                          <a class="page-link" href="<?= buildPageUrl($p, $searchTerm) ?>"><?= $p ?></a>
                        </li>
                      <?php endfor; ?>

                      <!-- Next -->
                      <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= buildPageUrl($currentPage + 1, $searchTerm) ?>"
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
                        Are you sure you want to delete this batch? This action cannot be undone.
                      </div>
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
            </div><!-- /.row -->
          </div>
        </div>
      </div>
      <br>
    </section>
  </div>
</div>
<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/batches/js/batches.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
