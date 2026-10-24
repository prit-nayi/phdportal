<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithErrorJson('Missing student ID.', 400);
}

$id = (int) $_GET['id'];

require_once __DIR__ . '/_upload.php';

// Text fields required on edit
$requiredText = [
  'batchId', 'ApplicationNo', 'ApplicationDate', 'ProgramName', 'Status',
  'StudentName', 'Surname', 'FathersName', 'MothersName',
  'PermanentAddress', 'PermanentCity', 'PermanentPinCode',
  'LocalAddress', 'LocalCity', 'LocalPinCode', 'LocalPhone',
  'MobileStudent', 'MobileParent', 'EmailStudent',
  'BirthDateStudent', 'Religion', 'Minority', 'Caste', 'Category',
  'Sex', 'NRI', 'HSCStream', 'English', 'AreaofResearch', 'isEligible',
  'SSC_Month_Year', 'SSC_BoardName', 'SSC_TotalMarks', 'SSC_ObtainedMarks', 'SSC_Percentage',
  'HSC_Month_Year', 'HSC_BoardName', 'HSC_TotalMarks', 'HSC_ObtainedMarks', 'HSC_Percentage',
  'UG_5th_7th_Month_Year', 'UG_5th_7th_BoardName', 'UG_5th_7th_TotalMarks',
  'UG_5th_7th_ObtainedMarks', 'UG_5th_7th_Percentage',
  'PG_Month_Year', 'PG_BoardName', 'PG_TotalMarks', 'PG_ObtainedMarks', 'PG_Percentage',
  // Edit-only status flags
  'isActive', 'isCancelled', 'isTransferred',
];

// Document fields (some were required on Add, but on Edit they might already exist in DB)
$allFileFields = [
  'StudentPhoto', 'SSCMarksheet', 'HSCMarksheet', '11thMarksheet',
  'UG_5th_7th_Marksheet', 'UG_6th_Marksheet', 'PGMarksheet',
  'PhotoIDProof', 'ResidentialProof', 'Payment',
  'EntranceExamScorecard', 'SchoolLeavingCertificate', 'FirstAttemptCertificate',
  'CasteCertificate', 'NonCreamyLayerCertificateissuedafter1_4_2018forSEBC',
  'PHCertificate', 'EBCCertificate', 'OfflineCode', 'GradeConversionPhoto',
];

if (!postHasAll($requiredText)) {
  exitWithErrorJson('Missing required text fields.', 400);
}

foreach (array_diff($requiredText, ['isActive', 'isCancelled', 'isTransferred', 'isEligible', 'batchId']) as $field) {
  if (trim($_POST[$field]) === '') {
    exitWithErrorJson(str_replace('_', ' ', $field) . ' is required.', 422);
  }
}

$batchId = (int) trim($_POST['batchId']);
$db      = Database::getInstance();

// Existence check
$record = $db->get("SELECT * FROM `phdStudents` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithErrorJson('Student not found.', 404);
}

// Validate batchId FK
$batch = $db->get("SELECT `id` FROM `phdBatches` WHERE `id` = ?", [$batchId]);
if (!$batch) {
  exitWithErrorJson('Selected batch does not exist.', 422);
}

$optionalText = [
  'Graduate', 'InstituteName', 'PANNoParent', 'PANNoStudent',
  'AadharNoParent', 'AadharNoStudent', 'NameofLocalGuardian',
  'MobileGuardian', 'EmailParent', 'EmailGuardian',
  'BloodGroup', 'Height_inCms', 'Weight_inKg', 'Activities',
  'HSC_SeatNo',
  '11th_Month_Year', '11th_BoardName', '11th_TotalMarks', '11th_ObtainedMarks', '11th_Percentage',
  'UG_5th_7th_SeatNo',
  'UG_6th_Month_Year', 'UG_6th_BoardName', 'UG_6th_TotalMarks', 'UG_6th_ObtainedMarks', 'UG_6th_Percentage', 'UG_6th_SeatNo',
  'PG_SeatNo',
  'CMAT_Month_Year', 'CMAT_ObtainedMarks', 'CMAT_Percentage',
  'CLAT_Month_Year', 'CLAT_ObtainedMarks', 'CLAT_Percentage',
  'CAT_Month_Year',  'CAT_ObtainedMarks',  'CAT_Percentage',
  'MAT_Month_Year',  'MAT_ObtainedMarks',  'MAT_Percentage',
  'ATMA_Month_Year', 'ATMA_ObtainedMarks', 'ATMA_Percentage',
  'GUJCET_Given', 'GUJCET_Month_Year', 'GUJCET_ObtainedMarks', 'GUJCET_Percentage',
  'JEE_Given',    'JEE_Month_Year',    'JEE_ObtainedMarks',    'JEE_Percentage',
  'UGCNET_Month_Year', 'UGCNET_ObtainedMarks', 'UGCNET_Percentage',
  'SLET_Month_Year',   'SLET_ObtainedMarks',   'SLET_Percentage',
  'UGCJRF_Month_Year', 'UGCJRF_ObtainedMarks', 'UGCJRF_Percentage',
  'MPhil_Month_Year',  'MPhil_ObtainedMarks',  'MPhil_Percentage',
  'Others_Month_Year', 'Others_ObtainedMarks',  'Others_Percentage',
  'cancelledOn', 'transferredOn', 'cancelledOrTransferredRemarks',
];

// Build dynamic SET clause
$setClauses = ['`batchId` = ?', '`isEligible` = ?', '`isActive` = ?', '`isCancelled` = ?', '`isTransferred` = ?'];
$params     = [
  $batchId,
  (int) trim($_POST['isEligible']),
  (int) trim($_POST['isActive']),
  (int) trim($_POST['isCancelled']),
  (int) trim($_POST['isTransferred']),
];

$allTextFields = array_diff(array_merge($requiredText, $optionalText), [
  'batchId', 'isEligible', 'isActive', 'isCancelled', 'isTransferred'
]);

foreach ($allTextFields as $f) {
  $setClauses[] = "`{$f}` = ?";
  $val = isset($_POST[$f]) ? trim($_POST[$f]) : null;
  $params[]     = ($val === '' || $val === null) ? null : $val;
}

// Process new file uploads
$uploadedPaths = [];
$filePaths = processStudentDocumentUploads($uploadedPaths);

$oldFilesToDelete = [];

foreach ($allFileFields as $f) {
  if (isset($filePaths[$f])) {
    $setClauses[] = "`{$f}` = ?";
    $params[]     = $filePaths[$f];
    
    // Remember the old file path so we can delete it later if DB update succeeds
    if (!empty($record->$f)) {
      $oldFilesToDelete[] = pathOf('/' . $record->$f);
    }
  }
}

$params[] = $id;
$setStr   = implode(', ', $setClauses);

try {
  $db->execute("UPDATE `phdStudents` SET $setStr WHERE `id` = ?", $params);
  
  // Cleanup old files now that DB update is successful
  foreach ($oldFilesToDelete as $oldPath) {
    if (file_exists($oldPath) && is_file($oldPath)) {
      @unlink($oldPath);
    }
  }
  
  exitWithSuccessJson('Student updated successfully.');
} catch (\Exception $e) {
  rollbackUploads($uploadedPaths);
  exitWithErrorJson('Failed to update student record.', 500);
}
