<?php

date_default_timezone_set('Asia/Kolkata');

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$inputData = json_decode(file_get_contents('php://input'), true);
if (!$inputData || !is_array($inputData)) {
    $inputData = $_POST;
}

$examId = isset($inputData['examId']) && ctype_digit((string)$inputData['examId']) ? (int)$inputData['examId'] : 0;
$studentId = isset($inputData['studentId']) && ctype_digit((string)$inputData['studentId']) ? (int)$inputData['studentId'] : 0;

if ($examId <= 0 || $studentId <= 0) {
    exitWithErrorJson('Admission Exam ID and Student ID are mandatory.', 400);
}

$db = Database::getInstance();

$existing = $db->get(
    "SELECT `id`, `presentation_ppt_file`, `proposalPresentationFileName` FROM `admissionExamResults` WHERE `studentId` = ? AND `examId` = ?",
    [$studentId, $examId]
);

if (!$existing) {
    exitWithErrorJson('No exam result record found for this student.', 404);
}

$oldPath = $existing->presentation_ppt_file ?? $existing->proposalPresentationFileName ?? null;
if ($oldPath && file_exists(pathOf('/' . ltrim($oldPath, '/')))) {
    @unlink(pathOf('/' . ltrim($oldPath, '/')));
}

$db->execute(
    "UPDATE `admissionExamResults` SET `presentation_ppt_file` = NULL, `proposalPresentationFileName` = NULL WHERE `id` = ?",
    [$existing->id]
);

exitWithSuccessJson('Presentation PPT file removed successfully.', [
    'studentId' => $studentId
]);
