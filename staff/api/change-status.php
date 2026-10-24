<?php

getOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithError(400);
}

$id = get('id');

$db = Database::getInstance();
$query = "UPDATE `staff` SET `isActive` = NOT `isActive` WHERE `id` = {$id}";
$db->execute($query);
