<?php

$title = "RDC";

Authorize::forRolesOrRedirect([Role::ADMIN]);
$db = Database::getInstance();

if (getHasAll(['search'])) {
  $search_term = get('search');
  $allRecords = $db->getAll("SELECT * FROM `scheduledRdcs` WHERE `name` LIKE ? ORDER BY `scheduledOn` DESC", ["%$search_term%"]);
} else {
  $allRecords = $db->getAll("SELECT * FROM `scheduledRdcs` ORDER BY `scheduledOn` DESC");
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
            <h1>RDC List</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item active">RDC List</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class="col text-right">
          <a role="button" class="btn btn-success" href="<?= urlOf('/rdc/add') ?>">Add New</a>
        </div>
      </div>
      <div class="row">&nbsp;</div>
      <div class="card card-outline card-info">
        <div class="card-body">
          <div class="col col-md-12">
            <div class="row">
              <div class="col col-lg-4 offset-lg-8 text-right mb-4 col-12">
                <form action="" method="get" class="input-group">
                  <input type="search" class="form-control" name="search" placeholder="Search..." autofocus
                    value="<?= isset($search_term) ? $search_term : "" ?>">
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
                      <th scope="col" class="item-list-title">Scheduled Date</th>
                      <th scope="col" class="item-list-actions" style="width: 28%">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($allRecords) == 0): ?>
                      <tr>
                        <td colspan="4" class="text-center">No records found</td>
                      </tr>
                    <?php else:
                      for ($i = 0; $i < count($allRecords); $i++): ?>
                        <tr>
                          <th scope="row" class="item-list-number"><?= $i + 1 ?></th>
                          <td class="item-list-name"><?= htmlspecialchars($allRecords[$i]->name) ?></td>
                          <td class="item-list-name"><?= date('d M Y', strtotime($allRecords[$i]->scheduledOn)) ?></td>
                              <td class="item-list-actions">
                            <div class="btn-group d-none d-lg-flex" role="group" aria-label="Actions">
                              <button type="button" class="btn btn-outline-secondary"
                                onclick="viewRdcSlots(<?= $allRecords[$i]->id ?>, '<?= htmlspecialchars(addslashes($allRecords[$i]->name)) ?>')"
                                data-toggle="tooltip" data-placement="bottom" title="View Panel & Slots">
                                <i class="far fa-eye"></i>
                              </button>
                              <a role="button" class="btn btn-warning"
                                href="<?= urlOf("/rdc/slots?id={$allRecords[$i]->id}") ?>" data-toggle="tooltip"
                                data-placement="bottom" title="Manage Slots">
                                <i class="far fa-clock"></i> Slots
                              </a>
                              <a role="button" class="btn btn-primary"
                                href="<?= urlOf("/rdc/edit?id={$allRecords[$i]->id}") ?>" data-toggle="tooltip"
                                data-placement="bottom" title="Edit">
                                <i class="far fa-edit"></i>
                              </a>
                              <a role="button" class="btn btn-danger"
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
                        Are you sure you want to delete this RDC?
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                        <button id="btn-yes" type="button" class="btn btn-danger" onclick="_delete()">Yes</button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Modal View RDC Accordion -->
                <div class="modal fade" id="modal-view-rdc" tabindex="-1" role="dialog" aria-labelledby="modal-view-rdc-title" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                      <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="modal-view-rdc-title"><i class="fas fa-layer-group mr-2"></i><span id="view-rdc-name">RDC Slots & Panel</span></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body" id="view-rdc-content" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Slots accordion dynamically injected here -->
                      </div>
                      <div class="modal-footer">
                        <a id="btn-modal-manage-slots" href="#" class="btn btn-warning"><i class="far fa-clock mr-1"></i> Manage Slots</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
<script src="<?= urlOf('/rdc/js/rdc.js?v=' . time()) ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>