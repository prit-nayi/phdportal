<?php

<<<<<<<< HEAD:rdc/add.php
$title = "Add RDC";
========
$title = "Add Batch";
>>>>>>>> phdGuides-and-guideAllocationPresentations:batches/add.php
Authorize::forRolesOrRedirect([Role::ADMIN]);

require_once pathOf('/@/includes/header.php');

?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6">
<<<<<<<< HEAD:rdc/add.php
            <h1>Add RDC</h1>
========
            <h1>Add Batch</h1>
>>>>>>>> phdGuides-and-guideAllocationPresentations:batches/add.php
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
<<<<<<<< HEAD:rdc/add.php
              <li class="breadcrumb-item"><a href="<?= urlOf('/rdc') ?>">RDC List</a></li>
========
              <li class="breadcrumb-item"><a href="<?= urlOf('/batches') ?>">Batches</a></li>
>>>>>>>> phdGuides-and-guideAllocationPresentations:batches/add.php
              <li class="breadcrumb-item active">Add</li>
            </ol>
          </div>
        </div>
        <div class="row">&nbsp;</div>
      </div>
    </section>
    <section class="content">

      <!-- Alert placeholder -->
      <div id="form-alert" style="display:none;"></div>

      <div class="row">
        <div class="col">
          <form id="batch-form" onsubmit="add(event);" novalidate>
            <div class="card card-outline card-info">
              <div class="card-body">

                <!-- Batch Name -->
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
<<<<<<<< HEAD:rdc/add.php
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" placeholder="Enter RDC name" required autofocus>
========
                      <label for="name">Batch Name</label>
                      <input
                        type="text"
                        class="form-control"
                        id="name"
                        name="name"
                        placeholder="e.g. PhD Batch 2024"
                        required
                        autofocus>
>>>>>>>> phdGuides-and-guideAllocationPresentations:batches/add.php
                    </div>
                  </div>
                </div>

                <!-- Enrollment Year + Fee Amount -->
                <div class="row mt-3">
                  <div class="col">
                    <div class="form-group">
<<<<<<<< HEAD:rdc/add.php
                      <label for="scheduledOn">Scheduled On</label>
                      <input type="date" class="form-control" id="scheduledOn" min="<?= date('Y-m-d') ?>" required>
========
                      <label for="enrollmentYear">Enrollment Year</label>
                      <input
                        type="number"
                        class="form-control"
                        id="enrollmentYear"
                        name="enrollmentYear"
                        placeholder="e.g. <?= date('Y') ?>"
                        required>
                    </div>
                  </div>
                  <div class="col">
                    <div class="form-group">
                      <label for="feeAmount">Fee Amount (₹)</label>
                      <input
                        type="number"
                        class="form-control"
                        id="feeAmount"
                        name="feeAmount"
                        placeholder="e.g. 50000.00"
                        step="any"
                        required>
>>>>>>>> phdGuides-and-guideAllocationPresentations:batches/add.php
                    </div>
                  </div>
                </div>

              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-success btn-submit" id="btn-submit">
                  <span class="btn-submit-text">Submit</span>
                  <span class="btn-submit-text-saved" style="display: none">Added!</span>
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
<<<<<<<< HEAD:rdc/add.php
<script src="<?= urlOf('/rdc/js/rdc.js') ?>"></script>
========
<script src="<?= urlOf('/batches/js/batches.js') ?>"></script>
>>>>>>>> phdGuides-and-guideAllocationPresentations:batches/add.php
<?php require_once pathOf('/@/includes/footer.php'); ?>
