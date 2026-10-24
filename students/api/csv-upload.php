<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);
$userId = $_SESSION['id'] ?? null;

if (!$userId) {
    exitWithErrorJson('User session not found.', 401);
}

// ── 1. File validation ────────────────────────────────────────────────────────
if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
  $uploadError = $_FILES['csvFile']['error'] ?? -1;
  $errorMessages = [
    UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
    UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
    UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
    UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
    UPLOAD_ERR_NO_TMP_DIR => 'Server temporary directory is missing.',
    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
    UPLOAD_ERR_EXTENSION  => 'Upload blocked by server extension.',
  ];
  exitWithErrorJson($errorMessages[$uploadError] ?? 'File upload failed.', 400);
}

$file     = $_FILES['csvFile'];
$fileName = $file['name'];
$fileTmp  = $file['tmp_name'];
$fileSize = $file['size'];

// Extension check
if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'csv') {
  exitWithErrorJson('Only .csv files are accepted.', 400);
}

// Size limit: 5 MB
if ($fileSize > 5 * 1024 * 1024) {
  exitWithErrorJson('File size exceeds 5 MB limit.', 400);
}

// MIME check (accept text/csv, text/plain, application/octet-stream for robustness)
$mime = mime_content_type($fileTmp);
$allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/octet-stream', 'application/vnd.ms-excel'];
if (!in_array($mime, $allowedMimes, true)) {
  exitWithErrorJson("Invalid file type detected ($mime). Please upload a valid CSV.", 400);
}

// ── 2. Expected headers (must match csv-template.php order exactly) ───────────
$expectedHeaders = [
  'batchId', 'ApplicationNo', 'ApplicationDate', 'ProgramName', 'InstituteName',
  'Status', 'Graduate', 'AreaofResearch', 'isEligible',
  'StudentName', 'Surname', 'FathersName', 'MothersName', 'NameofLocalGuardian',
  'BirthDateStudent', 'Sex', 'Religion', 'Minority', 'Caste', 'Category',
  'BloodGroup', 'Height_inCms', 'Weight_inKg', 'NRI', 'HSCStream', 'English',
  'Activities', 'PANNoStudent', 'PANNoParent', 'AadharNoStudent', 'AadharNoParent',
  'MobileStudent', 'MobileParent', 'MobileGuardian', 'EmailStudent', 'EmailParent',
  'EmailGuardian', 'LocalPhone',
  'PermanentAddress', 'PermanentCity', 'PermanentPinCode',
  'LocalAddress', 'LocalCity', 'LocalPinCode',
  'SSC_Month_Year', 'SSC_BoardName', 'SSC_TotalMarks', 'SSC_ObtainedMarks', 'SSC_Percentage',
  'HSC_Month_Year', 'HSC_BoardName', 'HSC_TotalMarks', 'HSC_ObtainedMarks', 'HSC_Percentage', 'HSC_SeatNo',
  '11th_Month_Year', '11th_BoardName', '11th_TotalMarks', '11th_ObtainedMarks', '11th_Percentage',
  'UG_5th_7th_Month_Year', 'UG_5th_7th_BoardName', 'UG_5th_7th_TotalMarks',
  'UG_5th_7th_ObtainedMarks', 'UG_5th_7th_Percentage', 'UG_5th_7th_SeatNo',
  'UG_6th_Month_Year', 'UG_6th_BoardName', 'UG_6th_TotalMarks', 'UG_6th_ObtainedMarks',
  'UG_6th_Percentage', 'UG_6th_SeatNo',
  'PG_Month_Year', 'PG_BoardName', 'PG_TotalMarks', 'PG_ObtainedMarks', 'PG_Percentage', 'PG_SeatNo',
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
  'StudentPhoto', 'SSCMarksheet', 'HSCMarksheet', '11thMarksheet',
  'UG_5th_7th_Marksheet', 'UG_6th_Marksheet', 'PGMarksheet',
  'EntranceExamScorecard', 'SchoolLeavingCertificate', 'FirstAttemptCertificate',
  'CasteCertificate', 'NonCreamyLayerCertificateissuedafter1_4_2018forSEBC',
  'PhotoIDProof', 'ResidentialProof', 'PHCertificate', 'EBCCertificate',
  'Payment', 'OfflineCode', 'GradeConversionPhoto',
];

// Required columns (NOT NULL in schema, omitting userId which is handled separately)
$requiredCols = [
  'batchId', 'ApplicationNo', 'ApplicationDate', 'ProgramName', 'Status',
  'AreaofResearch', 'isEligible',
  'StudentName', 'Surname', 'FathersName', 'MothersName',
  'BirthDateStudent', 'Sex', 'Religion', 'Minority', 'Caste', 'Category',
  'NRI', 'HSCStream', 'English',
  'MobileStudent', 'MobileParent', 'EmailStudent', 'LocalPhone',
  'PermanentAddress', 'PermanentCity', 'PermanentPinCode',
  'LocalAddress', 'LocalCity', 'LocalPinCode',
  'SSC_Month_Year', 'SSC_BoardName', 'SSC_TotalMarks', 'SSC_ObtainedMarks', 'SSC_Percentage',
  'HSC_Month_Year', 'HSC_BoardName', 'HSC_TotalMarks', 'HSC_ObtainedMarks', 'HSC_Percentage',
  'UG_5th_7th_Month_Year', 'UG_5th_7th_BoardName', 'UG_5th_7th_TotalMarks',
  'UG_5th_7th_ObtainedMarks', 'UG_5th_7th_Percentage',
  'PG_Month_Year', 'PG_BoardName', 'PG_TotalMarks', 'PG_ObtainedMarks', 'PG_Percentage',
  'StudentPhoto', 'SSCMarksheet', 'HSCMarksheet', '11thMarksheet',
  'UG_5th_7th_Marksheet', 'UG_6th_Marksheet', 'PGMarksheet',
  'PhotoIDProof', 'ResidentialProof', 'Payment',
];

// ── 3. Open and parse CSV ─────────────────────────────────────────────────────
$handle = fopen($fileTmp, 'r');
if ($handle === false) {
  exitWithErrorJson('Could not read the uploaded file.', 500);
}

// Strip BOM if present
$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") {
  rewind($handle);
}

// Read header row
$rawHeaders = fgetcsv($handle);
if ($rawHeaders === false || count($rawHeaders) === 0) {
  fclose($handle);
  exitWithErrorJson('The CSV file appears to be empty or has no header row.', 400);
}

// Trim every header
$rawHeaders = array_map('trim', $rawHeaders);

// Build a map: column name → CSV index (only for columns we know about)
// Unknown columns (id, userId, isActive, etc.) are silently ignored.
$knownCols  = array_flip($expectedHeaders);   // name → position in $expectedHeaders
$colIndex   = [];                             // name → CSV column index
foreach ($rawHeaders as $idx => $hdr) {
  if (isset($knownCols[$hdr])) {
    $colIndex[$hdr] = $idx;
  }
}

// Verify every required column is present in the uploaded file
$missingRequired = [];
foreach ($requiredCols as $col) {
  if (!isset($colIndex[$col])) {
    $missingRequired[] = $col;
  }
}
if (!empty($missingRequired)) {
  fclose($handle);
  exitWithErrorJson(
    'CSV is missing required column(s): ' . implode(', ', array_slice($missingRequired, 0, 10)),
    400
  );
}

// ── 4. Process rows ───────────────────────────────────────────────────────────
$db = Database::getInstance();

// Cache valid batchIds to avoid repeated DB queries
$batchCache = [];

// Track ApplicationNos seen in this upload to catch intra-file duplicates
$seenAppNos = [];

$imported = 0;
$skipped  = 0;
$errors   = [];
$rowNum   = 1; // 1 = header, data rows start at 2

// Build INSERT statement once (columns excluding batchId and isEligible which are cast)
// We build it dynamically based on $expectedHeaders
$insertCols = array_merge(
    ['`userId`'],
    array_map(fn($h) => "`{$h}`", $expectedHeaders)
);
$insertPlaceholders = implode(', ', array_fill(0, count($expectedHeaders) + 1, '?'));
$insertColList = implode(', ', $insertCols);
$sql = "INSERT INTO `phdStudents` ($insertColList) VALUES ($insertPlaceholders)";

while (($row = fgetcsv($handle)) !== false) {
  $rowNum++;

  // Skip completely blank rows
  if (count(array_filter(array_map('trim', $row))) === 0) {
    continue;
  }

  // ── 4a. Build data map using column index positions ──────────────────────
  $data      = [];
  foreach ($colIndex as $col => $idx) {
    $data[$col] = isset($row[$idx]) ? trim($row[$idx]) : '';
  }
  // Fill any expected columns not present in the CSV with empty string
  foreach ($expectedHeaders as $col) {
    if (!array_key_exists($col, $data)) {
      $data[$col] = '';
    }
  }

  $rowErrors = [];

  // ── 4b. Required field checks ─────────────────────────────────────────────
  foreach ($requiredCols as $col) {
    if ($data[$col] === '') {
      $rowErrors[] = "'{$col}' is required.";
    }
  }

  // ── 4c. batchId must be numeric and exist ─────────────────────────────────
  if ($data['batchId'] !== '' && !ctype_digit($data['batchId'])) {
    $rowErrors[] = "'batchId' must be a numeric value.";
  } elseif ($data['batchId'] !== '') {
    $bid = (int) $data['batchId'];
    if (!array_key_exists($bid, $batchCache)) {
      $batchRecord       = $db->get("SELECT `id` FROM `phdBatches` WHERE `id` = ?", [$bid]);
      $batchCache[$bid]  = (bool) $batchRecord;
    }
    if (!$batchCache[$bid]) {
      $rowErrors[] = "'batchId' {$data['batchId']} does not exist in phdBatches.";
    }
  }

  // ── 4d. isEligible must be 0 or 1 ────────────────────────────────────────
  if ($data['isEligible'] !== '' && !in_array($data['isEligible'], ['0', '1'], true)) {
    $rowErrors[] = "'isEligible' must be 0 or 1.";
  }

  // ── 4e. Duplicate ApplicationNo check (DB + intra-file) ──────────────────
  if ($data['ApplicationNo'] !== '') {
    if (isset($seenAppNos[$data['ApplicationNo']])) {
      $rowErrors[] = "'ApplicationNo' '{$data['ApplicationNo']}' is duplicated within the uploaded file.";
    } else {
      $dup = $db->get(
        "SELECT `id` FROM `phdStudents` WHERE `ApplicationNo` = ?",
        [$data['ApplicationNo']]
      );
      if ($dup) {
        $rowErrors[] = "'ApplicationNo' '{$data['ApplicationNo']}' already exists in the database.";
      } else {
        $seenAppNos[$data['ApplicationNo']] = true;
      }
    }
  }

  // ── 4f. If any errors, skip this row ─────────────────────────────────────
  if (!empty($rowErrors)) {
    $errors[] = ['row' => $rowNum, 'messages' => $rowErrors];
    $skipped++;
    continue;
  }

  // ── 4g. Build values array in column order ────────────────────────────────
  $values = [$userId];
  foreach ($expectedHeaders as $col) {
    $val = $data[$col];
    // Cast numeric columns
    if ($col === 'batchId' || $col === 'isEligible') {
      $values[] = (int) $val;
    } else {
      // Store empty strings as NULL for optional columns
      $values[] = ($val === '') ? null : $val;
    }
  }

  // ── 4h. Insert ────────────────────────────────────────────────────────────
  try {
    $db->execute($sql, $values);
    $imported++;
  } catch (\Exception $e) {
    $errors[] = ['row' => $rowNum, 'messages' => ['Database error: ' . $e->getMessage()]];
    $skipped++;
  }
}

fclose($handle);

// ── 5. Return JSON result ─────────────────────────────────────────────────────
header('Content-Type: application/json');
echo json_encode([
  'imported' => $imported,
  'skipped'  => $skipped,
  'errors'   => $errors,
]);
exit;
