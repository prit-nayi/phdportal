<?php

deleteOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithErrorJson('Missing student ID.', 400);
}

$id = (int) $_GET['id'];
$db = Database::getInstance();

$record = $db->get("SELECT `id` FROM `phdStudents` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithErrorJson('Student not found.', 404);
}

try {
  $db->execute("DELETE FROM `phdStudents` WHERE `id` = ?", [$id]);
  exitWithSuccessJson('Student deleted successfully.');
} catch (\Exception $e) {
  $msg = $e->getMessage();
  // FK constraint violation → the student has related records
  if (strpos($msg, 'foreign key constraint') !== false || strpos($msg, '1451') !== false) {
    exitWithErrorJson('Cannot delete this student because they have related records (exam results, fee payments, etc.). Please remove those records first.', 409);
  }
  exitWithErrorJson('Failed to delete student.', 500);
}
