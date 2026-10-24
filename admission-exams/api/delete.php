<?php

deleteOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithErrorJson('Missing exam ID.', 400);
}

$id = (int) $_GET['id'];

$db = Database::getInstance();

// Retrieve exam record to make sure it exists
$record = $db->get("SELECT `id` FROM `admissionExams` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithErrorJson('Admission exam not found.', 404);
}

// Delete database record
$db->execute("DELETE FROM `admissionExams` WHERE `id` = ?", [$id]);

exitWithSuccessJson('Admission exam deleted successfully.');
