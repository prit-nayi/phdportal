<?php

postOrExit();

Authorize::forRolesOrExit([Role::ADMIN]);

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

$db = Database::getInstance();

$db->execute(
  "INSERT INTO `phdGuides` (`name`, `designation`, `maximumNumberOfScholars`, `isActive`) VALUES (?, ?, ?, ?)",
  [$name, $designation, $maxCapacity, $isActive ? 1 : 0]
);

exitWithSuccessJson('Guide added successfully.');
