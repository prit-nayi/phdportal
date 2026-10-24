<?php

deleteOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithError(400);
}

$id = get('id');
$db = Database::getInstance();

$record = $db->get("SELECT `imageFileName` FROM `staff` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithError(404);
}

if ($record->imageFileName) {
  unlink(pathOf("/@/uploads/staff/{$record->imageFileName}"));
}

$db->execute("DELETE FROM `staff` WHERE `id` = ?", [$id]);
