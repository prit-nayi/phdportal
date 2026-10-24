<?php

date_default_timezone_set('Asia/Kolkata');

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

require_once __DIR__ . '/_upload.php';

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

$existingRecord = $db->get("SELECT * FROM `admissionExamResults` WHERE `id` = ?", [$id]);
if (!$existingRecord) {
    exitWithErrorJson('Admission exam result record not found.', 444);
}

$studentId = isset($_POST['studentId']) && ctype_digit((string)$_POST['studentId']) ? (int)$_POST['studentId'] : (int)$existingRecord->studentId;
$examId = isset($_POST['examId']) && ctype_digit((string)$_POST['examId']) ? (int)$_POST['examId'] : (int)$existingRecord->examId;

if ($studentId <= 0 || $examId <= 0) {
    exitWithErrorJson('Student and Exam selection are required.', 422);
}

// Validate Student exists
$student = $db->get("SELECT `id`, `batchId` FROM `phdStudents` WHERE `id` = ?", [$studentId]);
if (!$student) {
    exitWithErrorJson('Selected student does not exist.', 422);
}

// Validate Exam exists
$exam = $db->get("SELECT `id`, `batchId`, `maximumMarksOfWrittenExam`, `maximumMarksOfPresentation` FROM `admissionExams` WHERE `id` = ?", [$examId]);
if (!$exam) {
    exitWithErrorJson('Selected admission exam does not exist.', 422);
}

// Check for duplicate result excluding this record
$isDuplicate = $db->exists("SELECT `id` FROM `admissionExamResults` WHERE `studentId` = ? AND `examId` = ? AND `id` != ?", [$studentId, $examId, $id]);
if ($isDuplicate) {
    exitWithErrorJson('An exam result already exists for this student in the selected exam.', 409);
}

// Validate Marks
$obtainedWritten = isset($_POST['obtainedMarksInWrittenExam']) && $_POST['obtainedMarksInWrittenExam'] !== '' ? (float)$_POST['obtainedMarksInWrittenExam'] : null;
$obtainedPresentation = isset($_POST['obtainedMarksInPresentation']) && $_POST['obtainedMarksInPresentation'] !== '' ? (float)$_POST['obtainedMarksInPresentation'] : null;

if ($obtainedWritten !== null) {
    if ($obtainedWritten < 0) {
        exitWithErrorJson('Obtained written exam marks cannot be negative.', 422);
    }
    if ($exam->maximumMarksOfWrittenExam !== null && $obtainedWritten > (float)$exam->maximumMarksOfWrittenExam) {
        exitWithErrorJson("Obtained written marks ($obtainedWritten) cannot exceed maximum marks ({$exam->maximumMarksOfWrittenExam}).", 422);
    }
}

if ($obtainedPresentation !== null) {
    if ($obtainedPresentation < 0) {
        exitWithErrorJson('Obtained presentation marks cannot be negative.', 422);
    }
    if ($exam->maximumMarksOfPresentation !== null && $obtainedPresentation > (float)$exam->maximumMarksOfPresentation) {
        exitWithErrorJson("Obtained presentation marks ($obtainedPresentation) cannot exceed maximum marks ({$exam->maximumMarksOfPresentation}).", 422);
    }
}

$hasPassed = isset($_POST['hasPassed']) && ($_POST['hasPassed'] === '1' || $_POST['hasPassed'] === 'true' || $_POST['hasPassed'] === 'on') ? 1 : 0;

$uploadedPaths = [];
$newProposalFileName = processProposalPresentationUpload($uploadedPaths);

$finalProposalFileName = $existingRecord->proposalPresentationFileName;
if ($newProposalFileName !== null) {
    $finalProposalFileName = $newProposalFileName;
}

try {
    $db->execute(
        "UPDATE `admissionExamResults` 
         SET `studentId` = ?,
             `examId` = ?,
             `obtainedMarksInWrittenExam` = ?,
             `obtainedMarksInPresentation` = ?,
             `hasPassed` = ?,
             `proposalPresentationFileName` = ?
         WHERE `id` = ?",
        [
            $studentId,
            $examId,
            $obtainedWritten,
            $obtainedPresentation,
            $hasPassed,
            $finalProposalFileName,
            $id
        ]
    );

    // If new file was uploaded successfully, remove old file from disk if it existed
    if ($newProposalFileName !== null && !empty($existingRecord->proposalPresentationFileName)) {
        $oldFilePath = pathOf('/' . $existingRecord->proposalPresentationFileName);
        if (file_exists($oldFilePath) && is_file($oldFilePath)) {
            @unlink($oldFilePath);
        }
    }

    exitWithSuccessJson('Admission exam result updated successfully.');
} catch (\Throwable $e) {
    rollbackUploads($uploadedPaths);
    exitWithErrorJson('Database error: ' . $e->getMessage(), 500);
}
