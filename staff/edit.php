<?php

$title = "Edit Staff";

Authorize::forRolesOrRedirect([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithRedirect('/staff');
}

$id = get('id');
$db = Database::getInstance();
$record = $db->get("SELECT * FROM `staff` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithRedirect('/staff');
}

$defaultImageUrl = isset($record->imageFileName)
  ? urlOf('/@/uploads/staff/' . $record->imageFileName)
  : urlOf('/@/assets/img/default-user.png');

require_once pathOf('/@/includes/header.php');

?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit Staff</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/staff') ?>">Staff</a></li>
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
          <form onsubmit="edit(<?= $id ?>, event);">
            <div class="card card-outline card-info">
              <div class="card-body">
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" placeholder="Enter name"
                        value="<?= $record->name ?>" required autofocus>
                    </div>
                  </div>
                  <div class="col">
                    <div class="form-group">
                      <label for="staffType">Staff Type</label>
                      <select class="form-control" id="staffType" required>
                        <option value="Teaching" <?= $record->staffType == 'Teaching' ? 'selected' : '' ?>>Teaching</option>
                        <option value="Non-Teaching - Admin" <?= $record->staffType == 'Non-Teaching - Admin' ? 'selected' : '' ?>>Non-Teaching - Admin</option>
                        <option value="Non-Teaching - Lab" <?= $record->staffType == 'Non-Teaching - Lab' ? 'selected' : '' ?>>Non-Teaching - Lab</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="emailAddress">Email Address</label>
                      <input type="email" class="form-control" id="emailAddress" placeholder="Enter email address"
                        value="<?= $record->emailAddress ?>" required>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="designation">Designation</label>
                      <input type="text" class="form-control" id="designation" placeholder="Enter designation" value="<?= $record->designation ?>" required>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="qualification">Qualification</label>
                      <input type="text" class="form-control" id="qualification" placeholder="Enter qualification" value="<?= $record->qualification ?>">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="experience">Experience</label>
                      <input type="text" class="form-control" id="experience" placeholder="Enter experience" value="<?= $record->experience ?>">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="specialization">Specialization</label>
                      <input type="text" class="form-control" id="specialization" placeholder="Enter specialization" value="<?= $record->specialization ?>">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="researchArea">Research Area</label>
                      <input type="text" class="form-control" id="researchArea" placeholder="Enter research area" value="<?= $record->researchArea ?>">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="briefDescription">Brief Description</label>
                      <textarea class="form-control" id="briefDescription" placeholder="Enter brief description"><?= $record->briefDescription ?></textarea>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="websiteLink">Website Link</label>
                      <input type="url" class="form-control" id="websiteLink" placeholder="Enter website link" value="<?= $record->websiteLink ?>">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="googleScholarLink">Google Scholar Link</label>
                      <input type="url" class="form-control" id="googleScholarLink"
                        placeholder="Enter google scholar link" value="<?= $record->googleScholarLink ?>">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="linkedinLink">LinkedIn Link</label>
                      <input type="url" class="form-control" id="linkedinLink" placeholder="Enter LinkedIn profile link" value="<?= $record->linkedinLink ?>">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="researchgateLink">ResearchGate Link</label>
                      <input type="url" class="form-control" id="researchgateLink" placeholder="Enter researchgate link" value="<?= $record->researchgateLink ?>">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="orcidLink">ORCID Link</label>
                      <input type="url" class="form-control" id="orcidLink" placeholder="Enter orcid link" value="<?= $record->orcidLink ?>">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="scopusLink">Scopus Link</label>
                      <input type="url" class="form-control" id="scopusLink" placeholder="Enter scopus link" value="<?= $record->scopusLink ?>">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
                      <label for="image">Picture</label>
                      <div class="input-group">
                        <div class="custom-file">
                          <input style="cursor: pointer;" type="file" accept=".jpg,.jpeg,.png" class="custom-file-input" id="image" onchange="imageSelected();">
                          <label class="custom-file-label" for="image">Select image...</label>
                        </div>
                        <div class="input-group-append">
                          <button type="button" class="btn btn-outline-danger" onclick="clearImage('<?= $defaultImageUrl ?>');">Clear</button>
                          <?php if ($record->imageFileName): ?>
                            <button type="button" class="btn btn-danger ml-1" onclick="deleteImage(<?= $id ?>);">Remove</button>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col">
                    <div>
                      <b>
                        <p>Preview</p>
                      </b>
                    </div>
                    <div class="input-group">
                      <div class="container">
                        <div id="image-preview" class="row">
                          <img src="<?= $defaultImageUrl ?>" alt="preview photo" class="preview-item" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-success btn-submit">
                  <span class="btn-submit-text">Submit</span>
                  <span class="btn-submit-text-saved" style="display: none">Updated!</span>
                  <div class="btn-submit-spinner spinner-border spinner-border-sm" role="status" style="display: none">
                    <span class="sr-only">Loading...</span>
                  </div>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>
</div>
<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/staff/js/staff.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>