<?php

date_default_timezone_set('Asia/Kolkata');

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$examId = isset($_POST['examId']) && ctype_digit((string)$_POST['examId']) ? (int)$_POST['examId'] : 0;
$studentId = isset($_POST['studentId']) && ctype_digit((string)$_POST['studentId']) ? (int)$_POST['studentId'] : 0;

if ($examId <= 0 || $studentId <= 0) {
    exitWithErrorJson('Admission Exam ID and Student ID are mandatory.', 400);
}

if (!isset($_FILES['ppt_file']) || $_FILES['ppt_file']['error'] === UPLOAD_ERR_NO_FILE) {
    exitWithErrorJson('No file selected to replace.', 400);
}

$file = $_FILES['ppt_file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    exitWithErrorJson('File upload error code: ' . $file['error'], 400);
}

$maxSizeBytes = 20 * 1024 * 1024; // 20 MB
if ($file['size'] > $maxSizeBytes) {
    exitWithErrorJson('Maximum file size exceeded (Max 20MB).', 422);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$disallowedExtensions = ['php', 'exe', 'bat', 'cmd', 'sh', 'js', 'vbs', 'jar', 'phar', 'phtml', 'pl', 'cgi'];
if ($ext !== '' && in_array($ext, $disallowedExtensions, true)) {
    exitWithErrorJson("Executable/script file format (.{$ext}) is not allowed.", 422);
}

$baseUploadDir = pathOf('/uploads/presentation-ppt');
if (!is_dir($baseUploadDir)) {
    if (!mkdir($baseUploadDir, 0777, true)) {
        exitWithErrorJson('Failed to create upload directory.', 500);
    }
}

$newFilename = 'presentation_' . $studentId . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
$targetPath = $baseUploadDir . '/' . $newFilename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    exitWithErrorJson('Failed to save uploaded presentation file.', 500);
}

$relativePath = 'uploads/presentation-ppt/' . $newFilename;
$db = Database::getInstance();

$existing = $db->get(
    "SELECT `id`, `presentation_ppt_file`, `proposalPresentationFileName` FROM `admissionExamResults` WHERE `studentId` = ? AND `examId` = ?",
    [$studentId, $examId]
);

if ($existing) {
    // Unlink old physical file
    $oldPath = $existing->presentation_ppt_file ?? $existing->proposalPresentationFileName ?? null;
    if ($oldPath && file_exists(pathOf('/' . ltrim($oldPath, '/')))) {
        @unlink(pathOf('/' . ltrim($oldPath, '/')));
    }

    $db->execute(
        "UPDATE `admissionExamResults` SET `presentation_ppt_file` = ?, `proposalPresentationFileName` = ? WHERE `id` = ?",
        [$relativePath, $relativePath, $existing->id]
    );
} else {
    $db->execute(
        "INSERT INTO `admissionExamResults` (`studentId`, `examId`, `presentation_ppt_file`, `proposalPresentationFileName`) VALUES (?, ?, ?, ?)",
        [$studentId, $examId, $relativePath, $relativePath]
    );
}

exitWithSuccessJson('Presentation PPT file replaced successfully.', [
    'studentId' => $studentId,
    'relativePath' => $relativePath,
    'fileUrl' => urlOf('/' . $relativePath)
]);
