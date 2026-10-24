<?php

postOrExit();

Authorize::forRolesOrExit([Role::ADMIN]);

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
  exitWithErrorJson('Invalid or missing Guide ID.', 400);
}

$id = (int) $_GET['id'];
$db = Database::getInstance();

$guide = $db->get("SELECT `id` FROM `phdGuides` WHERE `id` = ?", [$id]);

if (!$guide) {
  exitWithErrorJson('Guide not found.', 404);
}

$fields = ['name', 'designation', 'maximumNumberOfScholars'];

if (!postHasAll($fields)) {
  exitWithErrorJson('Missing required fields.', 400);
}

$name                    = trim($_POST['name'] ?? '');
$designation             = trim($_POST['designation'] ?? '');
$maximumNumberOfScholars = trim($_POST['maximumNumberOfScholars'] ?? '');
$isActive                = isset($_POST['isActive']) ? (int) $_POST['isActive'] : 1;

if ($name === '') {
  exitWithErrorJson('Guide Name is required.', 422);
}

if (mb_strlen($name) > 255) {
  exitWithErrorJson('Guide Name cannot exceed 255 characters.', 422);
}

if ($designation === '') {
  exitWithErrorJson('Designation is required.', 422);
}

if (mb_strlen($designation) > 255) {
  exitWithErrorJson('Designation cannot exceed 255 characters.', 422);
}

if ($maximumNumberOfScholars === '' || !is_numeric($maximumNumberOfScholars)) {
  exitWithErrorJson('Maximum Number of Scholars must be a valid integer.', 422);
}

$maxCapacity = (int) $maximumNumberOfScholars;
if ($maxCapacity < 0) {
  exitWithErrorJson('Maximum Number of Scholars cannot be negative.', 422);
}

$db->execute(
  "UPDATE `phdGuides`
   SET `name` = ?, `designation` = ?, `maximumNumberOfScholars` = ?, `isActive` = ?
   WHERE `id` = ?",
  [$name, $designation, $maxCapacity, $isActive ? 1 : 0, $id]
);

exitWithSuccessJson('Guide updated successfully.');
