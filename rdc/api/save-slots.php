<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithError(400);
}

$rdcId = get('id');
$db = Database::getInstance();

// Verify RDC exists
$rdc = $db->get("SELECT `id` FROM `scheduledRdcs` WHERE `id` = ?", [$rdcId]);
if (!$rdc) {
  exitWithErrorJson("RDC not found.", 404);
}

// Decode JSON body
$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!isset($data['slots']) || !is_array($data['slots'])) {
  exitWithErrorJson("Invalid slots data.", 400);
}

$slots = $data['slots'];

// Validate each slot
foreach ($slots as $index => $slot) {
  $slotNum = $index + 1;

  if (empty(trim($slot['name'] ?? ''))) {
    exitWithErrorJson("Slot $slotNum: Slot name is required.", 400);
  }
  if (empty($slot['fromTime'] ?? '')) {
    exitWithErrorJson("Slot $slotNum: From time is required.", 400);
  }
  if (empty($slot['toTime'] ?? '')) {
    exitWithErrorJson("Slot $slotNum: To time is required.", 400);
  }
  if ($slot['fromTime'] >= $slot['toTime']) {
    exitWithErrorJson("Slot $slotNum: From time must be before To time.", 400);
  }
}

// To avoid duplicates, first delete existing allocations for this RDC's slots
$db->execute("DELETE FROM `staffRdcTimeSlotAllocation` WHERE `scheduledRdcTimeSlotId` IN (SELECT `id` FROM `scheduledRdcTimeSlots` WHERE `scheduledRdcId` = ?)", [$rdcId]);
$db->execute("DELETE FROM `externalRdcTimeSlotAllocation` WHERE `scheduledRdcTimeSlotId` IN (SELECT `id` FROM `scheduledRdcTimeSlots` WHERE `scheduledRdcId` = ?)", [$rdcId]);
$db->execute("DELETE FROM `studentRdcTimeSlotAllocation` WHERE `scheduledRdcTimeSlotId` IN (SELECT `id` FROM `scheduledRdcTimeSlots` WHERE `scheduledRdcId` = ?)", [$rdcId]);

// Delete existing slots for this RDC and re-insert
$db->execute("DELETE FROM `scheduledRdcTimeSlots` WHERE `scheduledRdcId` = ?", [$rdcId]);

foreach ($slots as $slot) {
  $name      = trim($slot['name']);
  $fromTime  = $slot['fromTime'];
  $toTime    = $slot['toTime'];

  $db->execute(
    "INSERT INTO `scheduledRdcTimeSlots` (`scheduledRdcId`, `name`, `fromTime`, `toTime`) VALUES (?, ?, ?, ?)",
    [$rdcId, $name, $fromTime, $toTime]
  );
  
  $slotId = $db->lastInsertId();

  if (isset($slot['guides']) && is_array($slot['guides'])) {
    foreach ($slot['guides'] as $staffId) {
      $db->execute("INSERT INTO `staffRdcTimeSlotAllocation` (`staffId`, `scheduledRdcTimeSlotId`) VALUES (?, ?)", [$staffId, $slotId]);
    }
  }

  if (isset($slot['externals']) && is_array($slot['externals'])) {
    foreach ($slot['externals'] as $extId) {
      $db->execute("INSERT INTO `externalRdcTimeSlotAllocation` (`externalPanelMemberId`, `scheduledRdcTimeSlotId`) VALUES (?, ?)", [$extId, $slotId]);
    }
  }

  if (isset($slot['students']) && is_array($slot['students'])) {
    foreach ($slot['students'] as $studentData) {
      $studentId = $studentData['studentId'];
      $turnNumber = $studentData['turnNumber'];
      $db->execute("INSERT INTO `studentRdcTimeSlotAllocation` (`studentId`, `scheduledRdcTimeSlotId`, `turnNumber`, `hasAttended`) VALUES (?, ?, ?, NULL)", [$studentId, $slotId, $turnNumber]);
    }
  }
}

exitWithSuccessJson("Slots saved successfully.");
