<?php

postOrExit();

Authorize::forRolesOrExit([Role::ADMIN]);

$fields = ['batchId', 'name', 'scheduledOn'];

if (!postHasAll($fields)) {
  exitWithErrorJson('Missing required fields.', 400);
}

$batchId     = trim($_POST['batchId'] ?? '');
$name        = trim($_POST['name'] ?? '');
$scheduledOn = trim($_POST['scheduledOn'] ?? '');

if ($batchId === '' || !ctype_digit($batchId)) {
  exitWithErrorJson('A valid Batch selection is required.', 422);
}

$db = Database::getInstance();
$batch = $db->get("SELECT `id` FROM `phdBatches` WHERE `id` = ?", [(int) $batchId]);
if (!$batch) {
  exitWithErrorJson('Selected Batch does not exist.', 422);
}

if ($name === '') {
  exitWithErrorJson('Presentation Name is required.', 422);
}

if (mb_strlen($name) > 255) {
  exitWithErrorJson('Presentation Name cannot exceed 255 characters.', 422);
}

if ($scheduledOn === '' || strtotime($scheduledOn) === false) {
  exitWithErrorJson('A valid Scheduled Date is required.', 422);
}

$formattedDate = date('Y-m-d', strtotime($scheduledOn));

$db->execute(
  "INSERT INTO `guideAllocationPresentations` (`batchId`, `name`, `scheduledOn`) VALUES (?, ?, ?)",
  [(int) $batchId, $name, $formattedDate]
);

exitWithSuccessJson('Presentation Schedule added successfully.');
