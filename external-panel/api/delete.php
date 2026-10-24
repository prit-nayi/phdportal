<?php
postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!postHasAll(['id'])) exitWithErrorJson("ID is required.", 400);

$db = Database::getInstance();
$id = post('id');

$db->execute("DELETE FROM `externalPanelMembers` WHERE `id` = ?", [$id]);
exitWithSuccess();
