<?php

$title = "Staff";

Authorize::forRolesOrRedirect([Role::ADMIN]);
$db = Database::getInstance();

if (getHasAll(['search'])) {
  $search_term = get('search');
  $allRecords = $db->getAll("SELECT * FROM `staff` WHERE `name` LIKE ? ORDER BY `orderIndex`", ["%$search_term%"]);
} else {
  $allRecords = $db->getAll("SELECT * FROM `staff` ORDER BY `orderIndex`");
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
            <h1>Staff</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/staff') ?>">Home</a></li>
              <li class="breadcrumb-item active">Staff</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class="col text-right">
          <a role="button" class="btn btn-success" href="<?= urlOf('/staff/add') ?>">Add New</a>
        </div>
      </div>
      <div class="row">&nbsp;</div>
      <div class="card card-outline card-info">
        <div class="card-body">
          <div class="col col-md-12">
            <div class="row">
              <div class="col col-lg-4 offset-lg-8 text-right mb-4 col-12">
                <form action="" method="get" class="input-group">
                  <input type="search" class="form-control" name="search" placeholder="Search..." autofocus value="<?= isset($search_term) ? $search_term : "" ?>">
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
                <table class="table table-striped item-list">
                  <thead>
                    <tr>
                      <th scope="col" class="item-list-number" style="width: 10%">Number</th>
                      <th scope="col" class="item-list-title">Name</th>
                      <th scope="col" class="item-list-title">Type</th>
                      <th scope="col" class="item-list-title">Designation</th>
                      <th scope="col" class="item-list-title">Experience</th>
                      <th scope="col" class="item-list-actions" style="width: 20%">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($allRecords) == 0): ?>
                      <tr>
                        <td colspan="6" class="text-center">No records found</td>
                      </tr>
                      <?php else: for ($i = 0; $i < count($allRecords); $i++): ?>
                        <tr>
                          <th scope="row" class="item-list-number"><?= $i + 1 ?></th>
                          <td class="item-list-name"><?= $allRecords[$i]->name ?></td>
                          <td class="item-list-name"><?= $allRecords[$i]->staffType ?></td>
                          <td class="item-list-name"><?= $allRecords[$i]->designation ?></td>
                          <td class="item-list-name"><?= $allRecords[$i]->experience ?></td>
                          <td class="item-list-actions">
                            <div class="btn-group d-none d-lg-flex" role="group" aria-label="Actions">
                              <button
                                class="btn <?= $allRecords[$i]->isActive ? "btn-warning" : "btn-success" ?>"
                                style="width: 60%;"
                                data-toggle="tooltip"
                                data-placement="bottom"
                                data-status="<?= $allRecords[$i]->isActive ? 'active' : 'inactive' ?>"
                                onclick="changeStatus(event, this, <?= $allRecords[$i]->id ?>);">
                                <span class="btn-submit-text"><?= $allRecords[$i]->isActive ? "Deactivate" : "Activate" ?></span>
                                <div class="btn-submit-spinner spinner-border spinner-border-sm" role="status" style="display: none;">
                                  <span class="sr-only">Loading...</span>
                                </div>
                              </button>
                              <a role="button" class="btn btn-primary" href="<?= urlOf("/staff/edit?id={$allRecords[$i]->id}") ?>" data-toggle="tooltip" data-placement="bottom" title="Edit">
                                <i class="far fa-edit"></i>
                              </a>
                              <a role="button" class="btn btn-danger" onclick="showDeleteConfirmation(<?= $allRecords[$i]->id ?>)" data-toggle="tooltip" data-placement="bottom" title="Delete">
                                <i class="far fa-trash-alt"></i>
                              </a>
                            </div>
                          </td>
                        </tr>
                      <?php endfor; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
                <div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modal-delete-title" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modal-delete-title">Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        Are you sure you want to delete?
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
<script src="<?= urlOf('/staff/js/staff.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>