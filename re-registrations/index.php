<?php

$title = "Re-Registrations";

Authorize::forRolesOrRedirect([Role::ADMIN]);
$db = Database::getInstance();

// ── Pagination ────────────────────────────────────────────────────────────────
$perPage     = 10;
$currentPage = isset($_GET['page']) && ctype_digit($_GET['page']) && (int) $_GET['page'] > 0
  ? (int) $_GET['page'] : 1;
$offset = ($currentPage - 1) * $perPage;

// ── Search ────────────────────────────────────────────────────────────────────
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

$baseJoin = "FROM `phdReRegistrations` r
             LEFT JOIN `phdStudents` s ON r.`studentId` = s.`id`
             LEFT JOIN `phdBatches` b ON s.`batchId` = b.`id`";

if ($searchTerm !== '') {
  $like = '%' . $searchTerm . '%';
  $totalRecord = $db->get(
    "SELECT COUNT(*) AS `total` $baseJoin
     WHERE s.`StudentName` LIKE ? OR s.`Surname` LIKE ?
        OR s.`ApplicationNo` LIKE ? OR b.`name` LIKE ?
        OR r.`reason` LIKE ?",
    [$like, $like, $like, $like, $like]
  );
  $totalRows = $totalRecord ? (int) $totalRecord->total : 0;

  $allRecords = $db->getAll(
    "SELECT r.`id`, r.`reason`, r.`createdAt`, r.`updatedAt`,
            s.`StudentName`, s.`Surname`, s.`ApplicationNo`,
            s.`isActive`, s.`isCancelled`, s.`isTransferred`,
            b.`name` AS batchName, b.`enrollmentYear`
     $baseJoin
     WHERE s.`StudentName` LIKE ? OR s.`Surname` LIKE ?
        OR s.`ApplicationNo` LIKE ? OR b.`name` LIKE ?
        OR r.`reason` LIKE ?
     ORDER BY r.`id` DESC
     LIMIT $perPage OFFSET $offset",
    [$like, $like, $like, $like, $like]
  );
} else {
  $totalRecord = $db->get("SELECT COUNT(*) AS `total` $baseJoin");
  $totalRows   = $totalRecord ? (int) $totalRecord->total : 0;

  $allRecords = $db->getAll(
    "SELECT r.`id`, r.`reason`, r.`createdAt`, r.`updatedAt`,
            s.`StudentName`, s.`Surname`, s.`ApplicationNo`,
            s.`isActive`, s.`isCancelled`, s.`isTransferred`,
            b.`name` AS batchName, b.`enrollmentYear`
     $baseJoin
     ORDER BY r.`id` DESC
     LIMIT $perPage OFFSET $offset"
  );
}

$totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;

// ── Helpers ───────────────────────────────────────────────────────────────────
function buildPageUrl(int $page, string $search): string
{
  $params = ['page' => $page];
  if ($search !== '') $params['search'] = $search;
  return '?' . http_build_query($params);
}

function readableDate(?string $ts): string
{
  if (!$ts) return '—';
  return date('d M Y, h:i A', strtotime($ts));
}

function studentStatusBadge(object $row): string
{
  if ($row->isCancelled)   return '<span class="badge badge-danger">Cancelled</span>';
  if ($row->isTransferred) return '<span class="badge badge-warning">Transferred</span>';
  if (!$row->isActive)     return '<span class="badge badge-secondary">Inactive</span>';
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
          <div class="col-sm-6"><h1>Re-Registrations</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item active">Re-Registrations</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">

      <div id="flash-alert" style="display:none;"></div>

      <div class="row">
        <div class="col text-right">
          <a role="button" class="btn btn-success" href="<?= urlOf('/re-registrations/add') ?>">Add New</a>
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
                  <input type="search" class="form-control" name="search"
                    placeholder="Search by student, app no, batch, reason..."
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
                      <th>Student Name</th>
                      <th>Application No</th>
                      <th>Batch</th>
                      <th>Status</th>
                      <th>Reason</th>
                      <th>Re-Registered On</th>
                      <th style="width:12%">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($allRecords) === 0): ?>
                      <tr><td colspan="8" class="text-center">No re-registration records found.</td></tr>
                    <?php else: ?>
                      <?php foreach ($allRecords as $i => $row): ?>
                        <tr>
                          <th scope="row"><?= $offset + $i + 1 ?></th>
                          <td><?= htmlspecialchars(($row->StudentName ?? '') . ' ' . ($row->Surname ?? ''), ENT_QUOTES) ?></td>
                          <td><?= htmlspecialchars($row->ApplicationNo ?? '—', ENT_QUOTES) ?></td>
                          <td>
                            <?php if ($row->batchName): ?>
                              <?= htmlspecialchars($row->batchName, ENT_QUOTES) ?> (<?= (int)($row->enrollmentYear ?? 0) ?>)
                            <?php else: ?>
                              —
                            <?php endif; ?>
                          </td>
                          <td><?= studentStatusBadge($row) ?></td>
                          <td>
                            <?php if ($row->reason): ?>
                              <span title="<?= htmlspecialchars($row->reason, ENT_QUOTES) ?>">
                                <?= htmlspecialchars(mb_strimwidth($row->reason, 0, 50, '...'), ENT_QUOTES) ?>
                              </span>
                            <?php else: ?>
                              <span class="text-muted">—</span>
                            <?php endif; ?>
                          </td>
                          <td style="font-size:0.85em;color:#6c757d;"><?= readableDate($row->createdAt) ?></td>
                          <td>
                            <div class="btn-group d-none d-lg-flex" role="group">
                              <a class="btn btn-primary btn-sm"
                                 href="<?= urlOf("/re-registrations/edit?id={$row->id}") ?>"
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
                  <nav aria-label="Re-registration pagination">
                    <ul class="pagination justify-content-center flex-wrap">
                      <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildPageUrl($currentPage - 1, $searchTerm) ?>">&laquo;</a>
                      </li>
                      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                          <a class="page-link" href="<?= buildPageUrl($p, $searchTerm) ?>"><?= $p ?></a>
                        </li>
                      <?php endfor; ?>
                      <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildPageUrl($currentPage + 1, $searchTerm) ?>">&raquo;</a>
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
                        Are you sure you want to delete this re-registration record? This action cannot be undone.
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
            </div>
          </div>
        </div>
      </div>
      <br>
    </section>
  </div>
</div>
<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/re-registrations/js/re-registrations.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
