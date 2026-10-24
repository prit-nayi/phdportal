<?php

$title = "Admission Exams";
Authorize::forRolesOrRedirect([Role::ADMIN]);

$db = Database::getInstance();

// ── Pagination Config ────────────────────────────────────────────────────────
$perPage = 20;
$currentPage = isset($_GET['page']) && ctype_digit($_GET['page']) && (int) $_GET['page'] > 0
  ? (int) $_GET['page']
  : 1;
$offset = ($currentPage - 1) * $perPage;

// ── Search Parameters ────────────────────────────────────────────────────────
$searchBatch = isset($_GET['search_batch']) ? trim($_GET['search_batch']) : '';
$searchWritten = isset($_GET['search_written']) ? trim($_GET['search_written']) : '';
$searchPresentation = isset($_GET['search_presentation']) ? trim($_GET['search_presentation']) : '';

// ── Sorting Parameters ───────────────────────────────────────────────────────
$allowedSorts = [
  'batch' => 'b.name',
  'created' => 'e.createdAt',
  'written' => 'e.dateAndTimeOfWrittenExam'
];

$sortBy = isset($_GET['sort']) && array_key_exists($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'created';
$sortColumn = $allowedSorts[$sortBy];

$sortDir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
$toggleDir = $sortDir === 'ASC' ? 'DESC' : 'ASC';

// ── Build Query ──────────────────────────────────────────────────────────────
$whereClauses = [];
$params = [];

if ($searchBatch !== '') {
  $whereClauses[] = "b.name LIKE ?";
  $params[] = '%' . $searchBatch . '%';
}

if ($searchWritten !== '') {
  $whereClauses[] = "DATE(e.dateAndTimeOfWrittenExam) = ?";
  $params[] = $searchWritten;
}

if ($searchPresentation !== '') {
  $whereClauses[] = "DATE(e.dateAndTimeOfPresentation) = ?";
  $params[] = $searchPresentation;
}

$whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : '';

// Get Total Records for Pagination
$countQuery = "SELECT COUNT(*) AS `total` 
               FROM `admissionExams` e 
               JOIN `phdBatches` b ON e.batchId = b.id 
               $whereSql";
$countRecord = $db->get($countQuery, count($params) > 0 ? $params : null);
$totalRows = $countRecord ? (int) $countRecord->total : 0;
$totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;

// Get Paginated and Sorted Records
$selectQuery = "SELECT e.*, b.name AS batchName, b.enrollmentYear 
                FROM `admissionExams` e 
                JOIN `phdBatches` b ON e.batchId = b.id 
                $whereSql 
                ORDER BY $sortColumn $sortDir 
                LIMIT $perPage OFFSET $offset";
$allRecords = $db->getAll($selectQuery, count($params) > 0 ? $params : null);

// ── Helper: preserve search and sort in page links ───────────────────────────
function buildPageUrl(int $page, string $sb, string $sw, string $sp, string $sort, string $dir): string
{
  $params = ['page' => $page];
  if ($sb !== '') $params['search_batch'] = $sb;
  if ($sw !== '') $params['search_written'] = $sw;
  if ($sp !== '') $params['search_presentation'] = $sp;
  $params['sort'] = $sort;
  $params['dir'] = $dir;

  return '?' . http_build_query($params);
}

// Local date format helper
function displayDateTime(?string $ts): string
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
            <h1>Admission Exams</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item active">Admission Exams</li>
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
          <a role="button" class="btn btn-success" href="<?= urlOf('/admission-exams/add') ?>">Add New</a>
        </div>
      </div>
      <div class="row">&nbsp;</div>
      <div class="card card-outline card-info">
        <div class="card-body">
          <div class="col col-md-12">

            <!-- Inline Filter -->
            <div class="row mb-4">
              <div class="col-12">
                <form action="" method="get" class="form-inline d-flex flex-wrap align-items-end" style="gap: 0.5rem;">
                  <input
                    type="search"
                    class="form-control"
                    name="search_batch"
                    placeholder="Batch name..."
                    autofocus
                    value="<?= htmlspecialchars($searchBatch, ENT_QUOTES) ?>">
                  <input
                    type="date"
                    class="form-control"
                    name="search_written"
                    title="Written Exam Date"
                    value="<?= htmlspecialchars($searchWritten, ENT_QUOTES) ?>">
                  <input
                    type="date"
                    class="form-control"
                    name="search_presentation"
                    title="Presentation Date"
                    value="<?= htmlspecialchars($searchPresentation, ENT_QUOTES) ?>">
                  <button type="submit" class="btn btn-info">
                    <i class="fa fa-search"></i>
                  </button>
                  <?php if ($searchBatch !== '' || $searchWritten !== '' || $searchPresentation !== ''): ?>
                    <a href="<?= urlOf('/admission-exams') ?>" class="btn btn-secondary" title="Reset Filters">
                      <i class="fas fa-undo"></i>
                    </a>
                  <?php endif; ?>
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
                      <th scope="col" class="item-list-title">
                        <a href="<?= buildPageUrl(1, $searchBatch, $searchWritten, $searchPresentation, 'batch', $sortBy === 'batch' ? $toggleDir : 'ASC') ?>" class="text-dark">
                          Batch Name
                          <?php if ($sortBy === 'batch'): ?>
                            <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                          <?php else: ?>
                            <i class="fas fa-sort text-muted ml-1"></i>
                          <?php endif; ?>
                        </a>
                      </th>
                      <th scope="col" class="item-list-title">
                        <a href="<?= buildPageUrl(1, $searchBatch, $searchWritten, $searchPresentation, 'written', $sortBy === 'written' ? $toggleDir : 'ASC') ?>" class="text-dark">
                          Written Exam Date
                          <?php if ($sortBy === 'written'): ?>
                            <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                          <?php else: ?>
                            <i class="fas fa-sort text-muted ml-1"></i>
                          <?php endif; ?>
                        </a>
                      </th>
                      <th scope="col" class="item-list-title">Presentation Date</th>
                      <th scope="col" class="item-list-title">Max Written</th>
                      <th scope="col" class="item-list-title">Max Presentation</th>
                      <th scope="col" class="item-list-title">Question Paper</th>
                      <th scope="col" class="item-list-title">
                        <a href="<?= buildPageUrl(1, $searchBatch, $searchWritten, $searchPresentation, 'created', $sortBy === 'created' ? $toggleDir : 'DESC') ?>" class="text-dark">
                          Created
                          <?php if ($sortBy === 'created'): ?>
                            <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                          <?php else: ?>
                            <i class="fas fa-sort text-muted ml-1"></i>
                          <?php endif; ?>
                        </a>
                      </th>
                      <th scope="col" class="item-list-actions" style="width: 14%">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($allRecords) === 0): ?>
                      <tr>
                        <td colspan="9" class="text-center">No records found</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($allRecords as $i => $row): ?>
                        <tr>
                          <th scope="row" class="item-list-number">
                            <?= $offset + $i + 1 ?>
                          </th>
                          <td class="item-list-name">
                            <?= htmlspecialchars($row->batchName, ENT_QUOTES) ?>
                            <span class="text-muted">(<?= (int) $row->enrollmentYear ?>)</span>
                          </td>
                          <td class="item-list-name"><?= displayDateTime($row->dateAndTimeOfWrittenExam) ?></td>
                          <td class="item-list-name"><?= displayDateTime($row->dateAndTimeOfPresentation) ?></td>
                          <td class="item-list-name"><?= htmlspecialchars($row->maximumMarksOfWrittenExam, ENT_QUOTES) ?></td>
                          <td class="item-list-name"><?= htmlspecialchars($row->maximumMarksOfPresentation, ENT_QUOTES) ?></td>
                          <td class="item-list-name">
                            <?php if ($row->writtenExamPaperFileName !== '' && $row->writtenExamPaperFileName !== null): ?>
                              <?= htmlspecialchars($row->writtenExamPaperFileName, ENT_QUOTES) ?>
                            <?php else: ?>
                              <span class="text-muted">—</span>
                            <?php endif; ?>
                          </td>
                          <td class="item-list-name" style="font-size:0.85em;color:#6c757d;">
                            <?= displayDateTime($row->createdAt) ?>
                          </td>
                          <td class="item-list-actions">
                            <div class="btn-group d-none d-lg-flex" role="group" aria-label="Actions">
                              <a role="button"
                                 class="btn btn-info"
                                 href="<?= urlOf("/admission-exams/view?id={$row->id}") ?>"
                                 data-toggle="tooltip"
                                 data-placement="bottom"
                                 title="View">
                                <i class="far fa-eye"></i>
                              </a>
                              <a role="button"
                                 class="btn btn-primary"
                                 href="<?= urlOf("/admission-exams/edit?id={$row->id}") ?>"
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
                  <nav aria-label="Exam pagination">
                    <ul class="pagination justify-content-center flex-wrap">

                      <!-- Previous -->
                      <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= buildPageUrl($currentPage - 1, $searchBatch, $searchWritten, $searchPresentation, $sortBy, $sortDir) ?>"
                           aria-label="Previous">
                          <span aria-hidden="true">&laquo;</span>
                        </a>
                      </li>

                      <!-- Page numbers -->
                      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                          <a class="page-link" href="<?= buildPageUrl($p, $searchBatch, $searchWritten, $searchPresentation, $sortBy, $sortDir) ?>"><?= $p ?></a>
                        </li>
                      <?php endfor; ?>

                      <!-- Next -->
                      <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="<?= buildPageUrl($currentPage + 1, $searchBatch, $searchWritten, $searchPresentation, $sortBy, $sortDir) ?>"
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
                        Are you sure you want to delete this admission exam schedule? This action cannot be undone.
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
<script src="<?= urlOf('/admission-exams/js/exams.js') ?>?v=<?= time() ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
