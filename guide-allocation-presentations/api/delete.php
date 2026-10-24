<?php

deleteOrExit();

Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id']) || !ctype_digit(get('id'))) {
  exitWithErrorJson('Invalid or missing Presentation ID.', 400);
}

$id = (int) get('id');
$db = Database::getInstance();

$presentation = $db->get("SELECT `id` FROM `guideAllocationPresentations` WHERE `id` = ?", [$id]);
if (!$presentation) {
  exitWithErrorJson('Presentation Schedule not found.', 404);
}

// ── Check if presentation is referenced by attendance or preference records ─
$attendanceRecord = $db->get("SELECT COUNT(*) AS `cnt` FROM `guideAllocationPresentationAttendance` WHERE `guideAllocationPresentationId` = ?", [$id]);
$hasAttendance    = $attendanceRecord && (int) $attendanceRecord->cnt > 0;

$preferencesRecord = $db->get("SELECT COUNT(*) AS `cnt` FROM `guidePreferences` WHERE `guideAllocationPresentationId` = ?", [$id]);
$hasPreferences    = $preferencesRecord && (int) $preferencesRecord->cnt > 0;

if ($hasAttendance || $hasPreferences) {
  exitWithErrorJson(
    'Cannot delete presentation schedule because dependent attendance or preference records exist for it.',
    409
  );
}

$db->execute("DELETE FROM `guideAllocationPresentations` WHERE `id` = ?", [$id]);

exitWithSuccessJson('Presentation Schedule deleted successfully.');
