<?php

getOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithError(400);
}

$rdcId = get('id');
$db = Database::getInstance();

$rdc = $db->get("SELECT * FROM `scheduledRdcs` WHERE `id` = ?", [$rdcId]);
if (!$rdc) {
  exitWithErrorJson("RDC not found.", 404);
}

$slots = $db->getAll("SELECT * FROM `scheduledRdcTimeSlots` WHERE `scheduledRdcId` = ? ORDER BY `fromTime` ASC", [$rdcId]);

$result = [];
foreach ($slots as $slot) {
  $sid = $slot->id;

  $guides = $db->getAll(
    "SELECT s.id, s.name, s.designation 
     FROM `staffRdcTimeSlotAllocation` a
     JOIN `phdguides` s ON s.id = a.staffId
     WHERE a.scheduledRdcTimeSlotId = ?
     ORDER BY s.name ASC",
    [$sid]
  );

  $externals = $db->getAll(
    "SELECT e.id, e.fullName as name, e.designation, e.organization 
     FROM `externalRdcTimeSlotAllocation` a
     JOIN `externalPanelMembers` e ON e.id = a.externalPanelMemberId
     WHERE a.scheduledRdcTimeSlotId = ?
     ORDER BY e.fullName ASC",
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

  $result[] = [
    'id'        => $slot->id,
    'name'      => $slot->name,
    'fromTime'  => date('h:i A', strtotime($slot->fromTime)),
    'toTime'    => date('h:i A', strtotime($slot->toTime)),
    'rawFrom'   => $slot->fromTime,
    'rawTo'     => $slot->toTime,
    'guides'    => $guides,
    'externals' => $externals,
    'students'  => $students,
  ];
}

exitWithSuccess([
  'status' => 'success',
  'rdc'    => $rdc,
  'slots'  => $result
]);
