<?php

// Set timezone to Asia/Kolkata
date_default_timezone_set('Asia/Kolkata');

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$fields = [
  'batchId',
  'dateAndTimeOfWrittenExam',
  'dateAndTimeOfPresentation',
  'maximumMarksOfWrittenExam',
  'maximumMarksOfPresentation'
];

if (!postHasAll($fields)) {
  exitWithErrorJson('Missing required fields.', 400);
}

$batchId = (int) post('batchId');
$writtenDate = trim(post('dateAndTimeOfWrittenExam'));
$presentationDate = trim(post('dateAndTimeOfPresentation'));
$maxWrittenMarks = post('maximumMarksOfWrittenExam');
$maxPresentationMarks = post('maximumMarksOfPresentation');

// Validations
if ($batchId <= 0) {
  exitWithErrorJson('Batch is mandatory.', 422);
}

if ($writtenDate === '') {
  exitWithErrorJson('Written Exam Date & Time cannot be empty.', 422);
}

if ($presentationDate === '') {
  exitWithErrorJson('Presentation Date & Time cannot be empty.', 422);
}

if ($maxWrittenMarks === '') {
  exitWithErrorJson('Maximum Written Marks cannot be empty.', 422);
}

if ($maxPresentationMarks === '') {
  exitWithErrorJson('Maximum Presentation Marks cannot be empty.', 422);
}

$writtenTime = strtotime($writtenDate);
$presentationTime = strtotime($presentationDate);

$db = Database::getInstance();

// Validate Batch exists
$batchExists = $db->exists("SELECT `id` FROM `phdBatches` WHERE `id` = ?", [$batchId]);
if (!$batchExists) {
  exitWithErrorJson('Selected Batch does not exist.', 422);
}

// Duplicate prevention check
$isDuplicate = $db->exists("SELECT `id` FROM `admissionExams` WHERE `batchId` = ?", [$batchId]);
if ($isDuplicate) {
  exitWithErrorJson('An admission exam has already been scheduled for this batch.', 409);
}

// Question Paper Name (optional text field)
$paperName = isset($_POST['writtenExamPaperFileName']) ? trim($_POST['writtenExamPaperFileName']) : '';

// Database Insert
$db->execute(
  "INSERT INTO `admissionExams` (
    `batchId`, 
    `dateAndTimeOfWrittenExam`, 
    `dateAndTimeOfPresentation`, 
    `maximumMarksOfWrittenExam`, 
    `maximumMarksOfPresentation`, 
    `writtenExamPaperFileName`
  ) VALUES (?, ?, ?, ?, ?, ?)",
  [
    $batchId,
    $writtenTime ? date('Y-m-d H:i:s', $writtenTime) : null,
    $presentationTime ? date('Y-m-d H:i:s', $presentationTime) : null,
    (float) $maxWrittenMarks,
    (float) $maxPresentationMarks,
    $paperName !== '' ? $paperName : null
  ]
);

exitWithSuccessJson('Admission exam created successfully.');
