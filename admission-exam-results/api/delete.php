<?php

date_default_timezone_set('Asia/Kolkata');

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$id = 0;
if (isset($_POST['id']) && ctype_digit((string)$_POST['id'])) {
    $id = (int)$_POST['id'];
} elseif (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
    $id = (int)$_GET['id'];
}

if ($id <= 0) {
    exitWithErrorJson('Invalid result ID.', 400);
}

$db = Database::getInstance();
$record = $db->get("SELECT `id`, `proposalPresentationFileName` FROM `admissionExamResults` WHERE `id` = ?", [$id]);

if (!$record) {
    exitWithErrorJson('Admission exam result record not found.', 444);
}

$db->execute("DELETE FROM `admissionExamResults` WHERE `id` = ?", [$id]);

// Delete uploaded file if present
if (!empty($record->proposalPresentationFileName)) {
    $filePath = pathOf('/' . $record->proposalPresentationFileName);
    if (file_exists($filePath) && is_file($filePath)) {
        @unlink($filePath);
    }
}

exitWithSuccessJson('Admission exam result deleted successfully.');
