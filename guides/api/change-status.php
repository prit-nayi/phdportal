<?php

getOrExit();

Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id']) || !ctype_digit(get('id'))) {
  exitWithErrorJson('Invalid or missing Guide ID.', 400);
}

$id = (int) get('id');
$db = Database::getInstance();

$guide = $db->get("SELECT `id`, `isActive` FROM `phdGuides` WHERE `id` = ?", [$id]);
if (!$guide) {
  exitWithErrorJson('Guide not found.', 404);
}

$db->execute("UPDATE `phdGuides` SET `isActive` = NOT `isActive` WHERE `id` = ?", [$id]);

exitWithSuccessJson('Guide status updated successfully.');
