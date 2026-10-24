<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$fields = [
  'name',
  'scheduledOn',
];

if (!postHasAll($fields)) {
  exitWithError(400);
}

$name = post('name');
$scheduledOn = post('scheduledOn');

// Validate: scheduled date must not be in the past
$today = date('Y-m-d');
if ($scheduledOn < $today) {
  exitWithErrorJson("Scheduled date cannot be in the past.", 400);
}

$db = Database::getInstance();

$query = "INSERT INTO `scheduledRdcs` (`name`, `scheduledOn`) VALUES (?, ?)";
$params = [$name, $scheduledOn];

$db->execute($query, $params);
