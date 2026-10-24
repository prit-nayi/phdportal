<?php

date_default_timezone_set('Asia/Kolkata');

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

require_once __DIR__ . '/_upload.php';

$studentId = isset($_POST['studentId']) && ctype_digit((string)$_POST['studentId']) ? (int)$_POST['studentId'] : 0;
$examId = isset($_POST['examId']) && ctype_digit((string)$_POST['examId']) ? (int)$_POST['examId'] : 0;

if ($studentId <= 0) {
    exitWithErrorJson('Student selection is mandatory.', 422);
}

if ($examId <= 0) {
    exitWithErrorJson('Admission Exam selection is mandatory.', 422);
}

$db = Database::getInstance();

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

// Check if student's batch matches exam's batch (if student has a batch)
if ($student->batchId && (int)$student->batchId !== (int)$exam->batchId) {
    exitWithErrorJson('Selected student does not belong to the batch for this admission exam.', 422);
}

// Check for duplicate result
$isDuplicate = $db->exists("SELECT `id` FROM `admissionExamResults` WHERE `studentId` = ? AND `examId` = ?", [$studentId, $examId]);
if ($isDuplicate) {
    exitWithErrorJson('An exam result already exists for this student in the selected exam.', 409);
}

// Validate Obtained Marks
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
$proposalFileName = processProposalPresentationUpload($uploadedPaths);

try {
    $db->execute(
        "INSERT INTO `admissionExamResults` (
            `studentId`,
            `examId`,
            `obtainedMarksInWrittenExam`,
            `obtainedMarksInPresentation`,
            `hasPassed`,
            `proposalPresentationFileName`
        ) VALUES (?, ?, ?, ?, ?, ?)",
        [
            $studentId,
            $examId,
            $obtainedWritten,
            $obtainedPresentation,
            $hasPassed,
            $proposalFileName
        ]
    );

    exitWithSuccessJson('Admission exam result recorded successfully.');
} catch (\Throwable $e) {
    rollbackUploads($uploadedPaths);
    exitWithErrorJson('Database error: ' . $e->getMessage(), 500);
}
