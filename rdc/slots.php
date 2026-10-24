<?php

$title = "Manage Slots";

Authorize::forRolesOrRedirect([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithRedirect('/rdc');
}

$id = get('id');
$db = Database::getInstance();

$rdc = $db->get("SELECT * FROM `scheduledRdcs` WHERE `id` = ?", [$id]);
if (!$rdc) {
  exitWithRedirect('/rdc');
}

// Fetch existing slots for this RDC
$existingSlots = $db->getAll("SELECT * FROM `scheduledRdcTimeSlots` WHERE `scheduledRdcId` = ? ORDER BY `fromTime` ASC", [$id]);

// Fetch entity lists for dropdowns
$staffList = $db->getAll("SELECT `id`, `name` FROM `phdguides` WHERE `isActive` = 1 ORDER BY `name` ASC");
$externalMembers = $db->getAll("SELECT `id`, `fullName` as `name` FROM `externalPanelMembers` ORDER BY `fullName` ASC");
$studentsList = $db->getAll("SELECT `id`, CONCAT(`StudentName`, ' ', `Surname`) as `name` FROM `phdStudents` ORDER BY `StudentName` ASC");

// Fetch existing allocations for each slot (so they render when page loads)
$slotAllocations = [];
foreach ($existingSlots as $slot) {
  $sid = $slot->id;

  $guides = $db->getAll(
    "SELECT s.id, s.name FROM `staffRdcTimeSlotAllocation` a
     JOIN `phdguides` s ON s.id = a.staffId
     WHERE a.scheduledRdcTimeSlotId = ?",
    [$sid]
  );

  $externals = $db->getAll(
    "SELECT e.id, e.fullName as name FROM `externalRdcTimeSlotAllocation` a
     JOIN `externalPanelMembers` e ON e.id = a.externalPanelMemberId
     WHERE a.scheduledRdcTimeSlotId = ?",
    [$sid]
  );

  $students = $db->getAll(
    "SELECT p.id, CONCAT(p.StudentName, ' ', p.Surname) as name, a.turnNumber
     FROM `studentRdcTimeSlotAllocation` a
     JOIN `phdStudents` p ON p.id = a.studentId
     WHERE a.scheduledRdcTimeSlotId = ?
     ORDER BY a.turnNumber ASC",
    [$sid]
  );

  $slotAllocations[$sid] = [
    'guides'    => $guides,
    'externals' => $externals,
    'students'  => $students,
  ];
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
            <h1>Manage Slots</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/rdc') ?>">RDC List</a></li>
              <li class="breadcrumb-item active">Slots</li>
            </ol>
          </div>
        </div>
        <div class="row">&nbsp;</div>
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class="col">

          <!-- RDC Info Card -->
          <div class="card card-outline card-warning mb-3">
            <div class="card-header">
              <h3 class="card-title">
                <i class="far fa-calendar-alt mr-2"></i>
                <strong><?= htmlspecialchars($rdc->name) ?></strong>
                &nbsp;&mdash;&nbsp;
                <span class="text-muted"><?= date('d M Y', strtotime($rdc->scheduledOn)) ?></span>
              </h3>
            </div>
          </div>

          <!-- Add Slot Card -->
          <div class="card card-outline card-success mb-3">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>Add New Slot</h3>
            </div>
            <div class="card-body">
              <div class="row align-items-end">
                <div class="col-md-4">
                  <div class="form-group mb-0">
                    <label>Slot Name</label>
                    <input type="text" class="form-control" id="new-slot-name" placeholder="e.g. Slot 1">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group mb-0">
                    <label>From Time</label>
                    <input type="time" class="form-control" id="new-slot-from" value="08:00">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group mb-0">
                    <label>To Time</label>
                    <input type="time" class="form-control" id="new-slot-to" value="11:30">
                  </div>
                </div>
                <div class="col-md-2 text-right">
                  <button type="button" class="btn btn-success w-100" onclick="addNewSlot()">
                    <i class="fas fa-plus mr-1"></i> Add Slot
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Success Alert (hidden by default) -->
          <div id="slots-success-alert" class="alert alert-success d-none" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>Saved successfully!</strong>
          </div>

          <!-- Accordion Container for Slots -->
          <div class="accordion" id="slots-accordion">
            <!-- Existing slots will be rendered here by JS -->
          </div>

          <div class="d-flex align-items-center mt-3 mb-4">
            <!-- Cancel: clears all slot data, does NOT navigate -->
            <button type="button" class="btn btn-secondary mr-2" onclick="cancelSlots()">
              <i class="fas fa-times mr-1"></i> Cancel
            </button>

            <!-- Save RDC -->
            <button type="button" class="btn btn-primary btn-save-slots" id="btn-save-slots"
              onclick="saveSlots(<?= $id ?>)">
              <span class="btn-save-text"><i class="fas fa-save mr-1"></i> Save RDC</span>
              <span class="btn-save-text-saved" style="display:none;"><i class="fas fa-check mr-1"></i>
                Saved!</span>
              <div class="btn-save-spinner spinner-border spinner-border-sm" role="status" style="display:none;">
                <span class="sr-only">Loading...</span>
              </div>
            </button>
          </div>

        </div>
      </div>
    </section>
  </div>
</div>

<style>
  #slots-success-alert {
    font-size: 1rem;
  }

  .card-header .card-title {
    font-size: 1rem;
    font-weight: 600;
  }

  .slot-accordion-card .card-header {
    background-color: #f4f6f9;
  }
</style>

<!-- Accordion Item Template (Hidden) -->
<template id="slot-accordion-template">
  <div class="card card-outline card-primary slot-accordion-card mb-2" data-slot-id="{SLOT_ID}">
    <div class="card-header" id="heading-{SLOT_ID}" data-toggle="collapse" data-target="#collapse-{SLOT_ID}"
      style="cursor: pointer;">
      <h5 class="card-title text-primary m-0 mt-1">
        <i class="fas fa-chevron-down mr-2" style="font-size: 14px;"></i>
        <span class="slot-title-display font-weight-bold">{SLOT_NAME}</span> &nbsp;|&nbsp; <span
          class="slot-time-display text-muted">{FROM_TIME} - {TO_TIME}</span>
      </h5>
      <div class="card-tools">
        <button type="button" class="btn btn-success btn-sm btn-save-slot" onclick="event.stopPropagation(); saveSlotAccordion(this)">
          <i class="fas fa-check mr-1"></i> Save
        </button>
        <button type="button" class="btn btn-primary btn-sm btn-edit-slot d-none" onclick="event.stopPropagation(); editSlot(this)">
          <i class="fas fa-edit mr-1"></i> Edit
        </button>
        <button type="button" class="btn btn-danger btn-sm" onclick="event.stopPropagation(); removeSlotAccordion(this)">
          <i class="fas fa-trash-alt mr-1"></i> Delete
        </button>
      </div>
    </div>
    <div id="collapse-{SLOT_ID}" class="collapse show" aria-labelledby="heading-{SLOT_ID}"
      data-parent="#slots-accordion">
      <div class="card-body">
        
        <div class="slot-view-mode d-none">
          <table class="table table-bordered table-sm mb-3">
            <tbody>
              <tr>
                <th class="bg-light" style="width: 20%;">Slot Name</th>
                <td style="width: 80%;" colspan="3" class="view-slot-name">{SLOT_NAME}</td>
              </tr>
              <tr>
                <th class="bg-light">From Time</th>
                <td class="view-from-time">{FROM_TIME}</td>
                <th class="bg-light">To Time</th>
                <td class="view-to-time">{TO_TIME}</td>
              </tr>
            </tbody>
          </table>
          <table class="table table-bordered table-sm mb-0">
            <thead>
              <tr class="bg-light">
                <th style="width: 33.33%;">Internal Panel Members</th>
                <th style="width: 33.33%;">External Panel Members</th>
                <th style="width: 33.33%;">Students</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="view-guides"></td>
                <td class="view-externals"></td>
                <td class="view-students"></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="slot-edit-mode">
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="form-group">
                <label>Slot Name</label>
                <input type="text" class="form-control slot-name-input" value="{SLOT_NAME}">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>From Time</label>
                <input type="time" class="form-control slot-from-input" value="{FROM_TIME}">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>To Time</label>
                <input type="time" class="form-control slot-to-input" value="{TO_TIME}">
              </div>
            </div>
          </div>

          <hr>

          <!-- Internal Panel Members -->
          <div class="mb-3">
            <h6 class="font-weight-bold"><button type="button" class="btn btn-sm btn-success mr-2"
                onclick="addEntityRow(this, 'guide')"><i class="fas fa-plus"></i></button>Internal Panel Members</h6>
            <div class="guide-container">
              <!-- rows will go here -->
            </div>
          </div>

          <!-- External Panel Members -->
          <div class="mb-3">
            <h6 class="font-weight-bold"><button type="button" class="btn btn-sm btn-success mr-2"
                onclick="addEntityRow(this, 'external')"><i class="fas fa-plus"></i></button>External Panel Members</h6>
            <div class="external-container">
              <!-- rows will go here -->
            </div>
          </div>

          <!-- Students -->
          <div class="mb-3">
            <h6 class="font-weight-bold"><button type="button" class="btn btn-sm btn-success mr-2"
                onclick="addEntityRow(this, 'student')"><i class="fas fa-plus"></i></button>Students</h6>
            <div class="student-container">
              <!-- rows will go here -->
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<template id="entity-row-template">
  <div class="row entity-row mb-2">
    <div class="col-md-11 select-wrapper">
      <div class="input-group entity-input-group">
        <select class="form-control entity-select">
          <option value="">Select...</option>
        </select>
        <div class="input-group-append d-none turn-badge-wrapper">
          <span class="input-group-text turn-badge bg-light font-weight-bold text-primary">Turn 1</span>
        </div>
      </div>
    </div>
    <div class="col-md-1 text-right">
      <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removeEntityRow(this)"><i
          class="fas fa-trash"></i></button>
    </div>
  </div>
</template>

<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script>
  // Load existing slots into JS variable so we can render them on load
  var existingSlots = <?= json_encode($existingSlots) ?>;
  var slotAllocations = <?= json_encode($slotAllocations) ?>;
  var staffList = <?= json_encode($staffList) ?>;
  var externalMembers = <?= json_encode($externalMembers) ?>;
  var studentsList = <?= json_encode($studentsList) ?>;
</script>
<script src="<?= urlOf('/rdc/js/rdc.js?v=' . time()) ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>