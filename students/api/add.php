<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

require_once __DIR__ . '/_upload.php';

// All NOT NULL columns that are required on insert (Text fields)
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
];

// File fields that are required
$requiredFiles = [
  'StudentPhoto', 'SSCMarksheet', 'HSCMarksheet', '11thMarksheet',
  'UG_5th_7th_Marksheet', 'UG_6th_Marksheet', 'PGMarksheet',
  'PhotoIDProof', 'ResidentialProof', 'Payment',
];

if (!postHasAll($requiredText)) {
  exitWithErrorJson('Missing required text fields.', 400);
}

// Validate each required field is non-empty after trim
foreach ($requiredText as $field) {
  if (trim($_POST[$field]) === '') {
    exitWithErrorJson(str_replace('_', ' ', $field) . ' is required.', 422);
  }
}

// Validate required files are present
foreach ($requiredFiles as $field) {
  if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
    exitWithErrorJson(str_replace('_', ' ', $field) . ' file is required.', 422);
  }
}

// Validate batchId exists
$batchId = (int) trim($_POST['batchId']);
$db      = Database::getInstance();
$batch   = $db->get("SELECT `id` FROM `phdBatches` WHERE `id` = ?", [$batchId]);
if (!$batch) {
  exitWithErrorJson('Selected batch does not exist.', 422);
}

// Collect all optional fields (text)
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
];

// Optional files
$optionalFiles = [
  'EntranceExamScorecard', 'SchoolLeavingCertificate', 'FirstAttemptCertificate',
  'CasteCertificate', 'NonCreamyLayerCertificateissuedafter1_4_2018forSEBC',
  'PHCertificate', 'EBCCertificate', 'OfflineCode', 'GradeConversionPhoto',
];

// Process File Uploads
$uploadedPaths = [];
$filePaths = processStudentDocumentUploads($uploadedPaths);

// Build column list and values — required first, then optional
$allTextFields = array_merge($requiredText, $optionalText);
$allTextFields = array_diff($allTextFields, ['batchId', 'isEligible']); // handled separately

$allFileFields = array_merge($requiredFiles, $optionalFiles);

$userId = $_SESSION['id'] ?? null;
if (!$userId) {
  rollbackUploads($uploadedPaths);
  exitWithErrorJson('User session not found.', 401);
}

$columns = ['`batchId`', '`isEligible`', '`userId`'];
$values  = [$batchId, (int) trim($_POST['isEligible']), $userId];

foreach ($allTextFields as $f) {
  $columns[] = "`{$f}`";
  $val = isset($_POST[$f]) ? trim($_POST[$f]) : null;
  $values[]  = ($val === '' || $val === null) ? null : $val;
}

foreach ($allFileFields as $f) {
  $columns[] = "`{$f}`";
  $values[]  = $filePaths[$f] ?? null; // Use DB null if file was optional and not provided
}

$placeholders = implode(', ', array_fill(0, count($columns), '?'));
$colList      = implode(', ', $columns);

try {
  $db->execute(
    "INSERT INTO `phdStudents` ($colList) VALUES ($placeholders)",
    $values
  );
  exitWithSuccessJson('Student added successfully.');
} catch (\Exception $e) {
  rollbackUploads($uploadedPaths);
  exitWithErrorJson('DB Error: ' . $e->getMessage(), 500);
}
