<?php

deleteOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithErrorJson('Missing re-registration ID.', 400);
}

$id = (int) $_GET['id'];
$db = Database::getInstance();

$record = $db->get("SELECT `id` FROM `phdReRegistrations` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithErrorJson('Re-registration record not found.', 404);
}

try {
  $db->execute("DELETE FROM `phdReRegistrations` WHERE `id` = ?", [$id]);
  exitWithSuccessJson('Re-registration deleted successfully.');
} catch (\Exception $e) {
  exitWithErrorJson('Failed to delete re-registration.', 500);
}
