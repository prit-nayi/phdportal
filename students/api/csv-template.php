<?php

Authorize::forRolesOrExit([Role::ADMIN]);

// ── CSV column definitions ────────────────────────────────────────────────────
// Ordered exactly as the phdStudents schema, omitting auto-managed columns
// (id, userId, isActive, isCancelled, isTransferred, cancelledOn, transferredOn,
//  cancelledOrTransferredRemarks, createdAt, updatedAt).
// The column "batchId" is exposed as "batchId" – admin must enter the numeric ID.

$headers = [
  // Application
  'batchId',
  'ApplicationNo',
  'ApplicationDate',
  'ProgramName',
  'InstituteName',
  'Status',
  'Graduate',
  'AreaofResearch',
  'isEligible',
  // Personal
  'StudentName',
  'Surname',
  'FathersName',
  'MothersName',
  'NameofLocalGuardian',
  'BirthDateStudent',
  'Sex',
  'Religion',
  'Minority',
  'Caste',
  'Category',
  'BloodGroup',
  'Height_inCms',
  'Weight_inKg',
  'NRI',
  'HSCStream',
  'English',
  'Activities',
  'PANNoStudent',
  'PANNoParent',
  'AadharNoStudent',
  'AadharNoParent',
  // Contact
  'MobileStudent',
  'MobileParent',
  'MobileGuardian',
  'EmailStudent',
  'EmailParent',
  'EmailGuardian',
  'LocalPhone',
  'PermanentAddress',
  'PermanentCity',
  'PermanentPinCode',
  'LocalAddress',
  'LocalCity',
  'LocalPinCode',
  // SSC
  'SSC_Month_Year',
  'SSC_BoardName',
  'SSC_TotalMarks',
  'SSC_ObtainedMarks',
  'SSC_Percentage',
  // HSC
  'HSC_Month_Year',
  'HSC_BoardName',
  'HSC_TotalMarks',
  'HSC_ObtainedMarks',
  'HSC_Percentage',
  'HSC_SeatNo',
  // 11th
  '11th_Month_Year',
  '11th_BoardName',
  '11th_TotalMarks',
  '11th_ObtainedMarks',
  '11th_Percentage',
  // UG 5th/7th
  'UG_5th_7th_Month_Year',
  'UG_5th_7th_BoardName',
  'UG_5th_7th_TotalMarks',
  'UG_5th_7th_ObtainedMarks',
  'UG_5th_7th_Percentage',
  'UG_5th_7th_SeatNo',
  // UG 6th
  'UG_6th_Month_Year',
  'UG_6th_BoardName',
  'UG_6th_TotalMarks',
  'UG_6th_ObtainedMarks',
  'UG_6th_Percentage',
  'UG_6th_SeatNo',
  // PG
  'PG_Month_Year',
  'PG_BoardName',
  'PG_TotalMarks',
  'PG_ObtainedMarks',
  'PG_Percentage',
  'PG_SeatNo',
  // Entrance exams
  'CMAT_Month_Year',    'CMAT_ObtainedMarks',    'CMAT_Percentage',
  'CLAT_Month_Year',    'CLAT_ObtainedMarks',    'CLAT_Percentage',
  'CAT_Month_Year',     'CAT_ObtainedMarks',     'CAT_Percentage',
  'MAT_Month_Year',     'MAT_ObtainedMarks',     'MAT_Percentage',
  'ATMA_Month_Year',    'ATMA_ObtainedMarks',    'ATMA_Percentage',
  'GUJCET_Given',       'GUJCET_Month_Year',     'GUJCET_ObtainedMarks',  'GUJCET_Percentage',
  'JEE_Given',          'JEE_Month_Year',         'JEE_ObtainedMarks',     'JEE_Percentage',
  'UGCNET_Month_Year',  'UGCNET_ObtainedMarks',  'UGCNET_Percentage',
  'SLET_Month_Year',    'SLET_ObtainedMarks',    'SLET_Percentage',
  'UGCJRF_Month_Year',  'UGCJRF_ObtainedMarks',  'UGCJRF_Percentage',
  'MPhil_Month_Year',   'MPhil_ObtainedMarks',   'MPhil_Percentage',
  'Others_Month_Year',  'Others_ObtainedMarks',  'Others_Percentage',
  // Documents
  'StudentPhoto',
  'SSCMarksheet',
  'HSCMarksheet',
  '11thMarksheet',
  'UG_5th_7th_Marksheet',
  'UG_6th_Marksheet',
  'PGMarksheet',
  'EntranceExamScorecard',
  'SchoolLeavingCertificate',
  'FirstAttemptCertificate',
  'CasteCertificate',
  'NonCreamyLayerCertificateissuedafter1_4_2018forSEBC',
  'PhotoIDProof',
  'ResidentialProof',
  'PHCertificate',
  'EBCCertificate',
  'Payment',
  'OfflineCode',
  'GradeConversionPhoto',
];

// ── Sample data row ───────────────────────────────────────────────────────────
$example = [
  // batchId, ApplicationNo, ApplicationDate, ProgramName, InstituteName, Status, Graduate, AreaofResearch, isEligible
  '1', 'PHD2024001', '2024-07-01', 'PhD Management', 'FCAIT', 'Active', 'No', 'Machine Learning', '1',
  // StudentName, Surname, FathersName, MothersName, NameofLocalGuardian, BirthDateStudent
  'Rahul', 'Sharma', 'Suresh Sharma', 'Meena Sharma', '', '2000-05-15',
  // Sex, Religion, Minority, Caste, Category, BloodGroup, Height_inCms, Weight_inKg
  'Male', 'Hindu', 'No', 'General', 'GEN', 'B+', '172', '68',
  // NRI, HSCStream, English, Activities
  'No', 'Science', 'Yes', 'Cricket',
  // PANNoStudent, PANNoParent, AadharNoStudent, AadharNoParent
  'ABCDE1234F', 'XYZDE5678G', '123456789012', '987654321098',
  // MobileStudent, MobileParent, MobileGuardian, EmailStudent, EmailParent, EmailGuardian
  '9876543210', '9876543211', '', 'rahul@example.com', 'suresh@example.com', '',
  // LocalPhone, PermanentAddress, PermanentCity, PermanentPinCode, LocalAddress, LocalCity, LocalPinCode
  '02717-123456', '12 MG Road Ahmedabad', 'Ahmedabad', '380001', '15 College Road', 'Ahmedabad', '380015',
  // SSC
  'Mar 2016', 'GSEB', '600', '540', '90.00',
  // HSC
  'Mar 2018', 'GSEB', '600', '570', '95.00', 'A12345',
  // 11th
  '', '', '', '', '',
  // UG 5th/7th
  'Nov 2021', 'Gujarat University', '700', '630', '90.00', 'B56789',
  // UG 6th
  '', '', '', '', '', '',
  // PG
  'Apr 2023', 'Gujarat University', '800', '740', '92.50', 'C98765',
  // Entrance exams (30 cols)
  '', '', '', // CMAT
  '', '', '', // CLAT
  '', '', '', // CAT
  '', '', '', // MAT
  '', '', '', // ATMA
  'No', '', '', '', // GUJCET
  'No', '', '', '', // JEE
  '', '', '', // UGCNET
  '', '', '', // SLET
  '', '', '', // UGCJRF
  '', '', '', // MPhil
  '', '', '', // Others
  // Documents
  'photo.jpg', 'ssc.pdf', 'hsc.pdf', '11th.pdf', 'ug57.pdf', 'ug6.pdf', 'pg.pdf',
  '', '', '', '', '',
  'aadhar.pdf', 'electricity.pdf', '', '', 'receipt001', '', '',
];

// ── Output CSV ────────────────────────────────────────────────────────────────
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="students_template_' . date('Ymd') . '.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// BOM for Excel UTF-8 compatibility
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');
fputcsv($output, $headers);
fputcsv($output, $example);
fclose($output);
exit;
