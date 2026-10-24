<?php

// Set timezone to Asia/Kolkata
date_default_timezone_set('Asia/Kolkata');

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$requiredFields = ['studentId', 'feeType', 'paidOnDate', 'chequeNumber', 'bankName', 'ifsc', 'branchName'];
if (!postHasAll($requiredFields)) {
  exitWithErrorJson('Missing required fields.', 400);
}

$studentId = (int) post('studentId');
$feeType = trim(post('feeType'));
$paidOnDate = trim(post('paidOnDate'));
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : null;

// Payment mode is forced to Bank (Cheque)
$paymentMode = 'Bank';

$chequeNumber = trim(post('chequeNumber'));
$bankName = trim(post('bankName'));
$ifsc = trim(post('ifsc'));
$branchName = trim(post('branchName'));

// Server-side Validations
if ($studentId <= 0) {
  exitWithErrorJson('Student is mandatory.', 422);
}

$db = Database::getInstance();
$studentExists = $db->exists("SELECT `id` FROM `phdStudents` WHERE `id` = ?", [$studentId]);
if (!$studentExists) {
  exitWithErrorJson('Selected student does not exist.', 422);
}

if ($feeType === '' || !in_array($feeType, ['Regular', 'Re-registration'])) {
  exitWithErrorJson('Valid Fee Type is required.', 422);
}

if ($paidOnDate === '') {
  exitWithErrorJson('Paid On Date is required.', 422);
}

$paidTime = strtotime($paidOnDate);
if ($paidTime === false) {
  exitWithErrorJson('Invalid Paid On Date format.', 422);
}

$today = date('Y-m-d');
$formattedPaidDate = date('Y-m-d', $paidTime);
if ($formattedPaidDate > $today) {
  exitWithErrorJson('Paid On Date cannot be a future date.', 422);
}

// Prevent duplicate payment for same student and feeType
$alreadyPaid = $db->exists(
  "SELECT 1 FROM `feePayments` WHERE `studentId` = ? AND `feeType` = ?",
  [$studentId, $feeType]
);
if ($alreadyPaid) {
  exitWithErrorJson('This student has already paid the ' . htmlspecialchars($feeType, ENT_QUOTES) . ' fee.', 422);
}

if ($chequeNumber === '') {
  exitWithErrorJson('Cheque Number is required.', 422);
}

if ($bankName === '') {
  exitWithErrorJson('Bank Name is required.', 422);
}

if ($ifsc === '') {
  exitWithErrorJson('IFSC Code is required.', 422);
}

if ($branchName === '') {
  exitWithErrorJson('Branch Name is required.', 422);
}

// Receipt File Upload Handling
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
$uploadDir = pathOf('/@/uploads/fee-payments');
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0755, true);
}

$receiptFileName = null;
$receiptFile = $_FILES['receipt'] ?? $_FILES['receiptFileName'] ?? null;
if ($receiptFile && isset($receiptFile['error']) && $receiptFile['error'] === UPLOAD_ERR_OK && $receiptFile['size'] > 0) {
  $ext = strtolower(pathinfo($receiptFile['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowedExtensions)) {
    exitWithErrorJson('Invalid Receipt file type. Only PDF, JPG, JPEG, and PNG are allowed.', 422);
  }
  $receiptFileName = uploadFileWithTimestampAsName($receiptFile, $uploadDir, 'receipt');
}

// Database Insert
$db->execute(
  "INSERT INTO `feePayments` (
    `studentId`,
    `feeType`,
    `paidOnDate`,
    `remarks`,
    `receiptFileName`,
    `paymentMode`,
    `chequeNumber`,
    `bankName`,
    `ifsc`,
    `branchName`,
    `onlineTransactionReferenceNumber`,
    `onlinePaymentProofFileName`
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)",
  [
    $studentId,
    $feeType,
    date('Y-m-d', $paidTime),
    $remarks !== '' ? $remarks : null,
    $receiptFileName,
    $paymentMode,
    $chequeNumber,
    $bankName,
    $ifsc,
    $branchName
  ]
);

exitWithSuccessJson('Fee payment added successfully.');
