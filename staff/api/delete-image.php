<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!postHasAll(['id'])) {
  exitWithError(400);
}

$id = post('id');
$db = Database::getInstance();

$staff = $db->get("SELECT * FROM `staff` WHERE `id` = ?", [$id]);

if (!$staff) {
  exitWithError(404);
}

if ($staff->imageFileName) {
  $filePath = pathOf("/@/uploads/staff/{$staff->imageFileName}");
  if (file_exists($filePath)) {
    unlink($filePath);
  }

  $db->execute("UPDATE `staff` SET `imageFileName` = NULL WHERE `id` = ?", [$id]);
}
