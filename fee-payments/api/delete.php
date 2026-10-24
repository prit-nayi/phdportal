<?php

deleteOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithErrorJson('Missing fee payment ID.', 400);
}

$id = (int) $_GET['id'];

$db = Database::getInstance();

// Retrieve fee payment record to ensure existence and obtain filenames for cleanup
$record = $db->get("SELECT * FROM `feePayments` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithErrorJson('Fee payment record not found.', 404);
}

// Delete uploaded receipt file if exists
if (!empty($record->receiptFileName)) {
  $receiptPath = pathOf("/@/uploads/fee-payments/{$record->receiptFileName}");
  if (file_exists($receiptPath)) {
    @unlink($receiptPath);
  }
}

// Delete uploaded online payment proof file if exists
if (!empty($record->onlinePaymentProofFileName)) {
  $proofPath = pathOf("/@/uploads/fee-payments/{$record->onlinePaymentProofFileName}");
  if (file_exists($proofPath)) {
    @unlink($proofPath);
  }
}

// Delete database record
$db->execute("DELETE FROM `feePayments` WHERE `id` = ?", [$id]);

exitWithSuccessJson('Fee payment deleted successfully.');
