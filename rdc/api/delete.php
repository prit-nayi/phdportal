<?php

deleteOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithError(400);
}

$id = get('id');
$db = Database::getInstance();

$record = $db->get("SELECT `id` FROM `scheduledRdcs` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithError(404);
}

$db->execute("DELETE FROM `scheduledRdcs` WHERE `id` = ?", [$id]);
