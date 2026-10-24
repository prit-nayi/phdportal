<?php

$title = "Edit Panel Member";
Authorize::forRolesOrRedirect([Role::ADMIN]);
$db = Database::getInstance();

if (!getHasAll(['id'])) exitWithRedirect('/external-panel');

$id = get('id');
$record = $db->get("SELECT * FROM `externalPanelMembers` WHERE `id` = ?", [$id]);
if (!$record) exitWithRedirect('/external-panel');

require_once pathOf('/@/includes/header.php');

?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit Panel Member</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/external-panel') ?>">External Panel</a></li>
              <li class="breadcrumb-item active">Edit</li>
            </ol>
          </div>
        </div>
        <div class="row">&nbsp;</div>
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class="col">
          <form onsubmit="edit(event, <?= $record->id ?>);">
            <div class="card card-outline card-info">
              <div class="card-body">
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="fullName">Full Name</label>
                      <input type="text" class="form-control" id="fullName" placeholder="Enter full name" value="<?= htmlspecialchars($record->fullName) ?>" required>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="designation">Designation</label>
                      <input type="text" class="form-control" id="designation" placeholder="Enter designation" value="<?= htmlspecialchars($record->designation) ?>" required>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="organization">Organization</label>
                      <input type="text" class="form-control" id="organization" placeholder="Enter organization" value="<?= htmlspecialchars($record->organization) ?>" required>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="mobileNumber">Mobile Number</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text bg-white">🇮🇳 +91</span>
                        </div>
                        <?php 
                          $mobile = preg_replace('/^\+?91/', '', $record->mobileNumber);
                        ?>
                        <input type="text" class="form-control" id="mobileNumber" placeholder="Enter 10 digit mobile number" value="<?= htmlspecialchars($mobile) ?>" maxlength="10" pattern="\d{10}" title="Please enter exactly 10 digits" required>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="emailAdderess">Email Address</label>
                      <input type="email" class="form-control" id="emailAdderess" placeholder="Enter email address" value="<?= htmlspecialchars($record->emailAdderess) ?>" required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer text-right">
                <button type="submit" class="btn btn-success btn-submit">
                  <span class="btn-submit-text">Submit</span>
                  <span class="btn-submit-text-saved" style="display: none">Saved!</span>
                  <div class="btn-submit-spinner spinner-border spinner-border-sm" role="status" style="display: none">
                    <span class="sr-only">Loading...</span>
                  </div>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
      <br>
    </section>
  </div>
</div>
<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/external-panel/js/external-panel.js?v=' . time()) ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
