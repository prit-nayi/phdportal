<?php

date_default_timezone_set('Asia/Kolkata');

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$examId = isset($_POST['examId']) && ctype_digit((string)$_POST['examId']) ? (int)$_POST['examId'] : 0;
if ($examId <= 0) {
    exitWithErrorJson('Admission Exam ID is mandatory.', 400);
}

$db = Database::getInstance();
$exam = $db->get("SELECT `id`, `batchId` FROM `admissionExams` WHERE `id` = ?", [$examId]);
if (!$exam) {
    exitWithErrorJson('Admission Exam not found.', 404);
}

if (empty($_FILES)) {
    exitWithErrorJson('No PPT files selected for upload.', 400);
}

$disallowedExtensions = ['php', 'exe', 'bat', 'cmd', 'sh', 'js', 'vbs', 'jar', 'phar', 'phtml', 'pl', 'cgi'];
$maxSizeBytes = 20 * 1024 * 1024; // 20 MB

$baseUploadDir = pathOf('/uploads/presentation-ppt');
if (!is_dir($baseUploadDir)) {
    if (!mkdir($baseUploadDir, 0777, true)) {
        exitWithErrorJson('Failed to create upload directory.', 500);
    }
}

$savedResults = [];
$errors = [];

// Parse uploaded files structure (e.g. $_FILES['ppt_file_<studentId>'])
foreach ($_FILES as $key => $fileInfo) {
    if (strpos($key, 'ppt_file_') === 0) {
        $studentId = (int) str_replace('ppt_file_', '', $key);
        if ($studentId <= 0 || $fileInfo['error'] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Student #{$studentId}: File upload error code {$fileInfo['error']}.";
            continue;
        }

        if ($fileInfo['size'] > $maxSizeBytes) {
            $errors[] = "Student #{$studentId}: Maximum file size exceeded (Max 20MB).";
            continue;
        }

        $ext = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        if ($ext !== '' && in_array($ext, $disallowedExtensions, true)) {
            $errors[] = "Student #{$studentId}: Executable/script file format (.{$ext}) is not allowed.";
            continue;
        }

        // Generate unique filename
        $newFilename = 'presentation_' . $studentId . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $targetPath = $baseUploadDir . '/' . $newFilename;

        if (!move_uploaded_file($fileInfo['tmp_name'], $targetPath)) {
            $errors[] = "Student #{$studentId}: Failed to save uploaded PPT file.";
            continue;
        }

        $relativePath = 'uploads/presentation-ppt/' . $newFilename;

        // Upsert database record
        $existing = $db->get(
            "SELECT `id`, `presentation_ppt_file`, `proposalPresentationFileName` FROM `admissionExamResults` WHERE `studentId` = ? AND `examId` = ?",
            [$studentId, $examId]
        );

        if ($existing) {
            // Delete old file if present
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

        $savedResults[] = [
            'studentId' => $studentId,
            'relativePath' => $relativePath,
            'fileUrl' => urlOf('/' . $relativePath)
        ];
    }
}

if (empty($savedResults) && !empty($errors)) {
    exitWithErrorJson(implode(' ', $errors), 422);
}

exitWithSuccessJson('Presentation PPT files uploaded successfully.', [
    'saved' => $savedResults,
    'warnings' => $errors
]);
