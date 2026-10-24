<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithErrorJson('Missing re-registration ID.', 400);
}

$id     = (int) $_GET['id'];
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : null;

// Store empty reason as NULL
if ($reason === '') {
  $reason = null;
}

$db = Database::getInstance();

// Verify record exists
$record = $db->get("SELECT `id` FROM `phdReRegistrations` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithErrorJson('Re-registration record not found.', 404);
}

try {
  $db->execute(
    "UPDATE `phdReRegistrations` SET `reason` = ? WHERE `id` = ?",
    [$reason, $id]
  );
  exitWithSuccessJson('Re-registration updated successfully.');
} catch (\Exception $e) {
  exitWithErrorJson('Failed to update re-registration.', 500);
}
