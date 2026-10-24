<?php

date_default_timezone_set('Asia/Kolkata');

getOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$examId = isset($_GET['examId']) && ctype_digit((string)$_GET['examId']) ? (int)$_GET['examId'] : 0;

if ($examId <= 0) {
    exitWithErrorJson('Admission Exam ID is mandatory.', 400);
}

$db = Database::getInstance();

$exam = $db->get(
    "SELECT e.`id`, e.`batchId`, e.`maximumMarksOfWrittenExam`, e.`maximumMarksOfPresentation`,
            b.`name` AS batchName, b.`enrollmentYear`
     FROM `admissionExams` e
     JOIN `phdBatches` b ON e.`batchId` = b.`id`
     WHERE e.`id` = ?",
    [$examId]
);

if (!$exam) {
    exitWithErrorJson('Admission exam not found.', 404);
}

$students = $db->getAll(
    "SELECT s.`id` AS studentId, s.`StudentName`, s.`Surname`, s.`ApplicationNo`,
            r.`obtainedMarksInWrittenExam`, r.`obtainedMarksInPresentation`, r.`hasPassed`,
            r.`presentation_ppt_file`, r.`proposalPresentationFileName`, r.`id` AS resultId
     FROM `phdStudents` s
     LEFT JOIN `admissionExamResults` r ON (s.`id` = r.`studentId` AND r.`examId` = ?)
     WHERE s.`batchId` = ? AND s.`isActive` = 1 AND s.`isCancelled` = 0 AND s.`isTransferred` = 0
     ORDER BY s.`StudentName` ASC, s.`Surname` ASC",
    [$examId, $exam->batchId]
);

$studentList = [];
foreach ($students as $stu) {
    $pptFile = $stu->presentation_ppt_file ?? $stu->proposalPresentationFileName ?? null;
    $pptUrl = $pptFile !== null ? urlOf('/' . ltrim($pptFile, '/')) : null;

    $studentList[] = [
        'studentId' => (int) $stu->studentId,
        'studentName' => trim($stu->StudentName . ' ' . $stu->Surname),
        'applicationNo' => $stu->ApplicationNo ?? '—',
        'enrollmentNo' => isset($stu->enrollmentNo) && $stu->enrollmentNo !== '' ? $stu->enrollmentNo : '—',
        'writtenMarks' => $stu->obtainedMarksInWrittenExam !== null ? (float)$stu->obtainedMarksInWrittenExam : null,
        'presentationMarks' => $stu->obtainedMarksInPresentation !== null ? (float)$stu->obtainedMarksInPresentation : null,
        'hasPassed' => $stu->hasPassed !== null ? (int)$stu->hasPassed : 0,
        'presentationPptFile' => $pptFile,
        'presentationPptUrl' => $pptUrl,
        'resultId' => $stu->resultId !== null ? (int)$stu->resultId : null
    ];
}

header('Content-Type: application/json');
http_response_code(200);
echo json_encode([
    'message' => 'Students retrieved successfully.',
    'data' => [
        'exam' => [
            'id' => (int) $exam->id,
            'batchId' => (int) $exam->batchId,
            'batchName' => $exam->batchName,
            'enrollmentYear' => (int) $exam->enrollmentYear,
            'maxWritten' => $exam->maximumMarksOfWrittenExam !== null ? (float)$exam->maximumMarksOfWrittenExam : null,
            'maxPresentation' => $exam->maximumMarksOfPresentation !== null ? (float)$exam->maximumMarksOfPresentation : null,
        ],
        'students' => $studentList
    ]
]);
exit();
