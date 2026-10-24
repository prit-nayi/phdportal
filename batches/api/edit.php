<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$fields = ['name', 'enrollmentYear', 'feeAmount'];

if (!getHasAll(['id']) || !postHasAll($fields)) {
  exitWithErrorJson('Missing required fields or ID.', 400);
}

$id             = (int) $_GET['id'];
$name           = trim($_POST['name']);
$enrollmentYear = trim($_POST['enrollmentYear']);
$feeAmount      = trim($_POST['feeAmount']);

if ($name === '') {
  exitWithErrorJson('Batch Name is required.', 422);
}
if ($enrollmentYear === '') {
  exitWithErrorJson('Enrollment Year is required.', 422);
}
if ($feeAmount === '') {
  exitWithErrorJson('Fee Amount is required.', 422);
}

$db = Database::getInstance();
$record = $db->get("SELECT `id` FROM `phdBatches` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithErrorJson('Batch not found.', 404);
}

$db->execute(
  "UPDATE `phdBatches` SET `name` = ?, `enrollmentYear` = ?, `feeAmount` = ? WHERE `id` = ?",
  [$name, $enrollmentYear, $feeAmount, $id]
);
