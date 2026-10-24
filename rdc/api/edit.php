<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$fields = [
  'name',
  'scheduledOn',
];

if (!getHasAll(['id']) || !postHasAll($fields)) {
  exitWithError(400);
}

$id          = get('id');
$name        = post('name');
$scheduledOn = post('scheduledOn');

// Validate: scheduled date must not be in the past
$today = date('Y-m-d');
if ($scheduledOn < $today) {
  exitWithErrorJson("Scheduled date cannot be in the past.", 400);
}

$db = Database::getInstance();
$record = $db->get("SELECT * FROM `scheduledRdcs` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithError(404);
}

$query  = "UPDATE `scheduledRdcs` SET `name` = ?, `scheduledOn` = ? WHERE `id` = ?";
$params = [$name, $scheduledOn, $id];

$db->execute($query, $params);
