<?php

date_default_timezone_set('Asia/Kolkata');

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

// Handle JSON payload or form-encoded payload
$inputData = json_decode(file_get_contents('php://input'), true);
if (!$inputData || !is_array($inputData)) {
    $inputData = $_POST;
}

$examId = isset($inputData['examId']) && ctype_digit((string)$inputData['examId']) ? (int)$inputData['examId'] : 0;
$tab = isset($inputData['tab']) ? strtolower(trim((string)$inputData['tab'])) : 'written';
$studentsData = isset($inputData['students']) && is_array($inputData['students']) ? $inputData['students'] : [];

if ($examId <= 0) {
    exitWithErrorJson('Admission Exam selection is mandatory.', 400);
}

if (!in_array($tab, ['written', 'presentation'], true)) {
    exitWithErrorJson('Invalid tab specified.', 400);
}

if (empty($studentsData)) {
    exitWithErrorJson('No student marks submitted.', 400);
}

$db = Database::getInstance();

$exam = $db->get(
    "SELECT `id`, `batchId`, `maximumMarksOfWrittenExam`, `maximumMarksOfPresentation` FROM `admissionExams` WHERE `id` = ?",
    [$examId]
);

if (!$exam) {
    exitWithErrorJson('Admission exam not found.', 404);
}

$maxAllowed = ($tab === 'written')
    ? ($exam->maximumMarksOfWrittenExam !== null ? (float)$exam->maximumMarksOfWrittenExam : null)
    : ($exam->maximumMarksOfPresentation !== null ? (float)$exam->maximumMarksOfPresentation : null);

$passingMarks = isset($inputData['passingMarks']) && $inputData['passingMarks'] !== '' && $inputData['passingMarks'] !== null ? (float)$inputData['passingMarks'] : null;

if ($passingMarks === null || $passingMarks < 0 || ($maxAllowed !== null && $passingMarks > $maxAllowed)) {
    $maxText = $maxAllowed !== null ? (string)$maxAllowed : 'maximum marks';
    exitWithErrorJson("Passing marks for $tab exam is mandatory and must be between 0 and $maxText.", 422);
}

// Validate all marks before executing DB transactions
foreach ($studentsData as $idx => $item) {
    $studentId = isset($item['studentId']) ? (int)$item['studentId'] : 0;
    if ($studentId <= 0) continue;

    $marksVal = isset($item['marks']) && $item['marks'] !== '' && $item['marks'] !== null ? (float)$item['marks'] : null;

    if ($marksVal !== null) {
        if ($marksVal < 0) {
            exitWithErrorJson("Student #$studentId: Marks cannot be negative.", 422);
        }
        if ($maxAllowed !== null && $marksVal > $maxAllowed) {
            exitWithErrorJson("Student #$studentId: Marks ($marksVal) cannot exceed maximum allowed ($maxAllowed).", 422);
        }
    }
}

// Perform DB upserts
try {
    foreach ($studentsData as $item) {
        $studentId = isset($item['studentId']) ? (int)$item['studentId'] : 0;
        if ($studentId <= 0) continue;

        $marksVal = isset($item['marks']) && $item['marks'] !== '' && $item['marks'] !== null ? (float)$item['marks'] : null;
        $hasPassed = isset($item['hasPassed']) && ($item['hasPassed'] == 1 || $item['hasPassed'] === true || $item['hasPassed'] === 'true') ? 1 : 0;

        $existing = $db->get(
            "SELECT `id`, `obtainedMarksInWrittenExam`, `obtainedMarksInPresentation` FROM `admissionExamResults` WHERE `studentId` = ? AND `examId` = ?",
            [$studentId, $examId]
        );

        if ($existing) {
            if ($tab === 'written') {
                $db->execute(
                    "UPDATE `admissionExamResults` SET `obtainedMarksInWrittenExam` = ?, `hasPassed` = ? WHERE `id` = ?",
                    [$marksVal, $hasPassed, $existing->id]
                );
            } else {
                $db->execute(
                    "UPDATE `admissionExamResults` SET `obtainedMarksInPresentation` = ?, `hasPassed` = ? WHERE `id` = ?",
                    [$marksVal, $hasPassed, $existing->id]
                );
            }
        } else {
            if ($tab === 'written') {
                $db->execute(
                    "INSERT INTO `admissionExamResults` (`studentId`, `examId`, `obtainedMarksInWrittenExam`, `hasPassed`) VALUES (?, ?, ?, ?)",
                    [$studentId, $examId, $marksVal, $hasPassed]
                );
            } else {
                $db->execute(
                    "INSERT INTO `admissionExamResults` (`studentId`, `examId`, `obtainedMarksInPresentation`, `hasPassed`) VALUES (?, ?, ?, ?)",
                    [$studentId, $examId, $marksVal, $hasPassed]
                );
            }
        }
    }

    $tabLabel = ($tab === 'written') ? 'Written exam' : 'Presentation';
    exitWithSuccessJson("$tabLabel marks saved successfully.");
} catch (\Throwable $e) {
    exitWithErrorJson('Database error: ' . $e->getMessage(), 500);
}
