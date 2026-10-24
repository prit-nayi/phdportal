<?php

postOrExit();

Authorize::forRolesOrExit([Role::ADMIN]);

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
  exitWithErrorJson('Invalid or missing Presentation ID.', 400);
}

$id = (int) $_GET['id'];
$db = Database::getInstance();

$presentation = $db->get("SELECT `id` FROM `guideAllocationPresentations` WHERE `id` = ?", [$id]);
if (!$presentation) {
  exitWithErrorJson('Presentation Schedule not found.', 404);
}

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
  "UPDATE `guideAllocationPresentations` SET `batchId` = ?, `name` = ?, `scheduledOn` = ? WHERE `id` = ?",
  [(int) $batchId, $name, $formattedDate, $id]
);

exitWithSuccessJson('Presentation Schedule updated successfully.');
