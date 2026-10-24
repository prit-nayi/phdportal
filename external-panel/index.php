<?php

$title = "External Panel Members";

Authorize::forRolesOrRedirect([Role::ADMIN]);
$db = Database::getInstance();

if (getHasAll(['search'])) {
  $search_term = get('search');
  $allRecords = $db->getAll("SELECT * FROM `externalPanelMembers` WHERE `fullName` LIKE ? ORDER BY `createdAt` DESC", ["%$search_term%"]);
} else {
  $allRecords = $db->getAll("SELECT * FROM `externalPanelMembers` ORDER BY `createdAt` DESC");
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
            <h1>External Panel Members</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item active">External Panel Members</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class="col text-right">
          <a role="button" class="btn btn-success" href="<?= urlOf('/external-panel/add') ?>">Add New</a>
        </div>
      </div>
      <div class="row">&nbsp;</div>
      <div class="card card-outline card-info">
        <div class="card-body">
          <div class="col col-md-12">
            <div class="row">
              <div class="col col-lg-4 offset-lg-8 text-right mb-4 col-12">
                <form action="" method="get" class="input-group">
                  <input type="search" class="form-control" name="search" placeholder="Search by name..." autofocus
                    value="<?= isset($search_term) ? htmlspecialchars($search_term) : "" ?>">
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-info">
                      <i class="fa fa-search"></i>
                    </button>
                  </div>
                </form>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <div class="table-responsive">
                  <table class="table table-striped item-list">
                    <thead>
                      <tr>
                        <th scope="col" style="width: 5%">#</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Designation</th>
                        <th scope="col">Organization</th>
                        <th scope="col">Mobile</th>
                        <th scope="col">Email</th>
                        <th scope="col" class="text-center" style="width: 15%">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($allRecords) == 0): ?>
                        <tr>
                          <td colspan="7" class="text-center">No records found</td>
                        </tr>
                      <?php else:
                        for ($i = 0; $i < count($allRecords); $i++): ?>
                          <tr>
                            <th scope="row"><?= $i + 1 ?></th>
                            <td><?= htmlspecialchars($allRecords[$i]->fullName) ?></td>
                            <td><?= htmlspecialchars($allRecords[$i]->designation) ?></td>
                            <td><?= htmlspecialchars($allRecords[$i]->organization) ?></td>
                            <td><?= htmlspecialchars($allRecords[$i]->mobileNumber) ?></td>
                            <td><?= htmlspecialchars($allRecords[$i]->emailAdderess) ?></td>
                            <td class="text-center">
                              <div class="btn-group d-none d-lg-flex justify-content-center" role="group"
                                aria-label="Actions">
                                <a role="button" class="btn btn-primary btn-sm"
                                  href="<?= urlOf("/external-panel/edit?id={$allRecords[$i]->id}") ?>"
                                  data-toggle="tooltip" data-placement="bottom" title="Edit">
                                  <i class="far fa-edit"></i>
                                </a>
                                <!-- Using same confirmation delete logic as staff -->
                                <a role="button" class="btn btn-danger btn-sm"
                                  onclick="showDeleteConfirmation(<?= $allRecords[$i]->id ?>)" data-toggle="tooltip"
                                  data-placement="bottom" title="Delete">
                                  <i class="far fa-trash-alt"></i>
                                </a>
                              </div>
                            </td>
                          </tr>
                        <?php endfor; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="modal-delete" tabindex="-1" role="dialog"
                  aria-labelledby="modal-delete-title" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modal-delete-title">Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        Are you sure you want to delete this panel member?
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                        <button id="btn-yes" type="button" class="btn btn-danger" onclick="_delete()">Yes</button>
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
<script src="<?= urlOf('/external-panel/js/external-panel.js?v=' . time()) ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>