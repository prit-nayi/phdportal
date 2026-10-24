<?php

$title = "Admission Exam Results";
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
$searchBatch   = isset($_GET['search_batch']) ? trim($_GET['search_batch']) : '';
$searchStatus  = isset($_GET['search_status']) ? trim($_GET['search_status']) : '';

// ── Sorting Parameters ───────────────────────────────────────────────────────
$allowedSorts = [
  'student'      => 's.StudentName',
  'batch'        => 'b.name',
  'written'      => 'r.obtainedMarksInWrittenExam',
  'presentation' => 'r.obtainedMarksInPresentation',
  'status'       => 'r.hasPassed',
  'created'      => 'r.createdAt'
];

$sortBy = isset($_GET['sort']) && array_key_exists($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'created';
$sortColumn = $allowedSorts[$sortBy];

$sortDir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
$toggleDir = $sortDir === 'ASC' ? 'DESC' : 'ASC';

// ── Build Query ──────────────────────────────────────────────────────────────
$whereClauses = [];
$params = [];

if ($searchStudent !== '') {
  $whereClauses[] = "(s.StudentName LIKE ? OR s.Surname LIKE ? OR s.ApplicationNo LIKE ?)";
  $params[] = '%' . $searchStudent . '%';
  $params[] = '%' . $searchStudent . '%';
  $params[] = '%' . $searchStudent . '%';
}

if ($searchBatch !== '') {
  $whereClauses[] = "b.name LIKE ?";
  $params[] = '%' . $searchBatch . '%';
}

if ($searchStatus !== '') {
  if ($searchStatus === 'pass') {
    $whereClauses[] = "r.hasPassed = 1";
  } elseif ($searchStatus === 'fail') {
    $whereClauses[] = "r.hasPassed = 0";
  }
}

$whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : '';

// Count Query
$countQuery = "SELECT COUNT(*) AS `total` 
               FROM `admissionExamResults` r 
               JOIN `phdStudents` s ON r.studentId = s.id 
               JOIN `admissionExams` e ON r.examId = e.id 
               JOIN `phdBatches` b ON e.batchId = b.id 
               $whereSql";
$countRecord = $db->get($countQuery, count($params) > 0 ? $params : null);
$totalRows = $countRecord ? (int) $countRecord->total : 0;
$totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;

// Select Query
$selectQuery = "SELECT r.*, 
                       s.StudentName, s.Surname, s.ApplicationNo, 
                       b.name AS batchName, b.enrollmentYear,
                       e.maximumMarksOfWrittenExam, e.maximumMarksOfPresentation,
                       e.dateAndTimeOfWrittenExam, e.dateAndTimeOfPresentation
                FROM `admissionExamResults` r 
                JOIN `phdStudents` s ON r.studentId = s.id 
                JOIN `admissionExams` e ON r.examId = e.id 
                JOIN `phdBatches` b ON e.batchId = b.id 
                $whereSql 
                ORDER BY $sortColumn $sortDir 
                LIMIT $perPage OFFSET $offset";
$allRecords = $db->getAll($selectQuery, count($params) > 0 ? $params : null);

// Helper for pagination links
function buildPageUrl(int $page, string $ss, string $sb, string $st, string $sort, string $dir): string
{
  $params = ['page' => $page];
  if ($ss !== '') $params['search_student'] = $ss;
  if ($sb !== '') $params['search_batch']   = $sb;
  if ($st !== '') $params['search_status']  = $st;
  $params['sort'] = $sort;
  $params['dir']  = $dir;

  return '?' . http_build_query($params);
}

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
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Admission Exam Results</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item active">Exam Results</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <!-- Flash Alert Placeholder -->
      <div id="flash-alert" style="display:none;"></div>

      <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-<?= isset($_GET['type']) && $_GET['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show text-left" role="alert">
          <i class="<?= isset($_GET['type']) && $_GET['type'] === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle' ?> mr-2"></i>
          <?= htmlspecialchars($_GET['msg'], ENT_QUOTES) ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <div class="row">
        <div class="col text-right">
          <a role="button" class="btn btn-success" href="<?= urlOf('/admission-exam-results/add') ?>">
            <i class="fas fa-plus mr-1"></i> Add Exam Result
          </a>
        </div>
      </div>
      <div class="row">&nbsp;</div>

      <div class="card card-outline card-info">
        <div class="card-body">
          <div class="col col-md-12">

            <!-- Filter Bar -->
            <div class="row mb-4">
              <div class="col-12">
                <form action="" method="get" class="form-inline d-flex flex-wrap align-items-end" style="gap: 0.5rem;">
                  <input
                    type="search"
                    class="form-control"
                    name="search_student"
                    placeholder="Student name or app no..."
                    value="<?= htmlspecialchars($searchStudent, ENT_QUOTES) ?>">
                  <input
                    type="search"
                    class="form-control"
                    name="search_batch"
                    placeholder="Batch name..."
                    value="<?= htmlspecialchars($searchBatch, ENT_QUOTES) ?>">
                  <select name="search_status" class="form-control">
                    <option value="">-- All Statuses --</option>
                    <option value="pass" <?= $searchStatus === 'pass' ? 'selected' : '' ?>>Passed</option>
                    <option value="fail" <?= $searchStatus === 'fail' ? 'selected' : '' ?>>Failed</option>
                  </select>
                  <button type="submit" class="btn btn-info" title="Search">
                    <i class="fa fa-search"></i>
                  </button>
                  <?php if ($searchStudent !== '' || $searchBatch !== '' || $searchStatus !== ''): ?>
                    <a href="<?= urlOf('/admission-exam-results') ?>" class="btn btn-secondary" title="Reset Filters">
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
                        <th scope="col" style="width: 5%">#</th>
                        <th scope="col">
                          <a href="<?= buildPageUrl(1, $searchStudent, $searchBatch, $searchStatus, 'student', $sortBy === 'student' ? $toggleDir : 'ASC') ?>" class="text-dark">
                            Student Name
                            <?php if ($sortBy === 'student'): ?>
                              <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                            <?php else: ?>
                              <i class="fas fa-sort text-muted ml-1"></i>
                            <?php endif; ?>
                          </a>
                        </th>
                        <th scope="col">
                          <a href="<?= buildPageUrl(1, $searchStudent, $searchBatch, $searchStatus, 'batch', $sortBy === 'batch' ? $toggleDir : 'ASC') ?>" class="text-dark">
                            Batch
                            <?php if ($sortBy === 'batch'): ?>
                              <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                            <?php else: ?>
                              <i class="fas fa-sort text-muted ml-1"></i>
                            <?php endif; ?>
                          </a>
                        </th>
                        <th scope="col">
                          <a href="<?= buildPageUrl(1, $searchStudent, $searchBatch, $searchStatus, 'written', $sortBy === 'written' ? $toggleDir : 'DESC') ?>" class="text-dark">
                            Written Marks
                            <?php if ($sortBy === 'written'): ?>
                              <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                            <?php else: ?>
                              <i class="fas fa-sort text-muted ml-1"></i>
                            <?php endif; ?>
                          </a>
                        </th>
                        <th scope="col">
                          <a href="<?= buildPageUrl(1, $searchStudent, $searchBatch, $searchStatus, 'presentation', $sortBy === 'presentation' ? $toggleDir : 'DESC') ?>" class="text-dark">
                            Presentation Marks
                            <?php if ($sortBy === 'presentation'): ?>
                              <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                            <?php else: ?>
                              <i class="fas fa-sort text-muted ml-1"></i>
                            <?php endif; ?>
                          </a>
                        </th>
                        <th scope="col">
                          <a href="<?= buildPageUrl(1, $searchStudent, $searchBatch, $searchStatus, 'status', $sortBy === 'status' ? $toggleDir : 'DESC') ?>" class="text-dark">
                            Result Status
                            <?php if ($sortBy === 'status'): ?>
                              <i class="fas fa-sort-<?= $sortDir === 'ASC' ? 'up' : 'down' ?> ml-1"></i>
                            <?php else: ?>
                              <i class="fas fa-sort text-muted ml-1"></i>
                            <?php endif; ?>
                          </a>
                        </th>
                        <th scope="col">Proposal Doc</th>
                        <th scope="col" style="width: 14%">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($allRecords) === 0): ?>
                        <tr>
                          <td colspan="8" class="text-center text-muted py-4">No admission exam results found.</td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($allRecords as $i => $row): ?>
                          <tr id="result-row-<?= (int)$row->id ?>">
                            <th><?= $offset + $i + 1 ?></th>
                            <td>
                              <strong><?= htmlspecialchars($row->StudentName . ' ' . $row->Surname, ENT_QUOTES) ?></strong>
                              <br><small class="text-muted">App No: <?= htmlspecialchars($row->ApplicationNo, ENT_QUOTES) ?></small>
                            </td>
                            <td>
                              <?= htmlspecialchars($row->batchName, ENT_QUOTES) ?>
                              <br><small class="text-muted">(<?= (int)$row->enrollmentYear ?>)</small>
                            </td>
                            <td>
                              <?= $row->obtainedMarksInWrittenExam !== null ? htmlspecialchars($row->obtainedMarksInWrittenExam, ENT_QUOTES) : '—' ?>
                              <?php if ($row->maximumMarksOfWrittenExam !== null): ?>
                                <small class="text-muted"> / <?= htmlspecialchars($row->maximumMarksOfWrittenExam, ENT_QUOTES) ?></small>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?= $row->obtainedMarksInPresentation !== null ? htmlspecialchars($row->obtainedMarksInPresentation, ENT_QUOTES) : '—' ?>
                              <?php if ($row->maximumMarksOfPresentation !== null): ?>
                                <small class="text-muted"> / <?= htmlspecialchars($row->maximumMarksOfPresentation, ENT_QUOTES) ?></small>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if ($row->hasPassed): ?>
                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Passed</span>
                              <?php else: ?>
                                <span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Failed</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if (!empty($row->proposalPresentationFileName)): ?>
                                <a href="<?= urlOf('/' . $row->proposalPresentationFileName) ?>" target="_blank" class="btn btn-xs btn-outline-primary" title="Download Proposal Presentation">
                                  <i class="fas fa-file-download mr-1"></i> View File
                                </a>
                              <?php else: ?>
                                <span class="text-muted">—</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <div class="btn-group" role="group">
                                <a role="button" class="btn btn-info btn-sm" href="<?= urlOf("/admission-exam-results/view?id={$row->id}") ?>" data-toggle="tooltip" title="View">
                                  <i class="far fa-eye"></i>
                                </a>
                                <a role="button" class="btn btn-primary btn-sm" href="<?= urlOf("/admission-exam-results/edit?id={$row->id}") ?>" data-toggle="tooltip" title="Edit">
                                  <i class="fas fa-pencil-alt"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= (int)$row->id ?>, '<?= htmlspecialchars(addslashes($row->StudentName . ' ' . $row->Surname), ENT_QUOTES) ?>');" data-toggle="tooltip" title="Delete">
                                  <i class="fas fa-trash-alt"></i>
                                </button>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
              <div class="row mt-3">
                <div class="col-sm-12 col-md-5">
                  <div class="dataTables_info" role="status" aria-live="polite">
                    Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalRows) ?> of <?= $totalRows ?> entries
                  </div>
                </div>
                <div class="col-sm-12 col-md-7">
                  <ul class="pagination pagination-sm m-0 float-right">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                      <a class="page-item page-link" href="<?= buildPageUrl($currentPage - 1, $searchStudent, $searchBatch, $searchStatus, $sortBy, $sortDir) ?>">&laquo;</a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                      <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                        <a class="page-item page-link" href="<?= buildPageUrl($p, $searchStudent, $searchBatch, $searchStatus, $sortBy, $sortDir) ?>"><?= $p ?></a>
                      </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                      <a class="page-item page-link" href="<?= buildPageUrl($currentPage + 1, $searchStudent, $searchBatch, $searchStatus, $sortBy, $sortDir) ?>">&raquo;</a>
                    </li>
                  </ul>
                </div>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-exclamation-triangle mr-2"></i> Confirm Delete</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete the exam result for <strong id="deleteStudentName"></strong>?
        <p class="text-danger mt-2 mb-0"><small>This action cannot be undone and associated files will be removed.</small></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deleteResult();">Delete</button>
      </div>
    </div>
  </div>
</div>

<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/admission-exam-results/js/exam-results.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
