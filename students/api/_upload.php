<?php

/**
 * Handles file uploads for the student module.
 * Returns an array of paths on success, or exits with JSON error on failure.
 * Populates $uploadedPaths array so they can be deleted on rollback.
 */
function processStudentDocumentUploads(array &$uploadedPaths): array
{
    $docs = [
        'StudentPhoto' => 'photos',
        'GradeConversionPhoto' => 'photos',
        'SSCMarksheet' => 'marksheets',
        'HSCMarksheet' => 'marksheets',
        '11thMarksheet' => 'marksheets',
        'UG_5th_7th_Marksheet' => 'marksheets',
        'UG_6th_Marksheet' => 'marksheets',
        'PGMarksheet' => 'marksheets',
        'SchoolLeavingCertificate' => 'certificates',
        'FirstAttemptCertificate' => 'certificates',
        'CasteCertificate' => 'certificates',
        'NonCreamyLayerCertificateissuedafter1_4_2018forSEBC' => 'certificates',
        'PHCertificate' => 'certificates',
        'EBCCertificate' => 'certificates',
        'PhotoIDProof' => 'documents',
        'ResidentialProof' => 'documents',
        'Payment' => 'documents',
        'OfflineCode' => 'documents',
        'EntranceExamScorecard' => 'documents',
    ];

    $maxSize = 5 * 1024 * 1024; // 5 MB
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    
    // Photo fields should only be images
    $photoAllowedMimeTypes = ['image/jpeg', 'image/png'];
    $photoAllowedExtensions = ['jpg', 'jpeg', 'png'];

    $baseUploadDir = pathOf('/uploads/students');
    $paths = [];

    foreach ($docs as $field => $folder) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES[$field];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                rollbackUploads($uploadedPaths);
                exitWithErrorJson("Error uploading {$field}.", 400);
            }

            if ($file['size'] > $maxSize) {
                rollbackUploads($uploadedPaths);
                exitWithErrorJson("{$field} exceeds the 5MB size limit.", 400);
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mime = mime_content_type($file['tmp_name']);

            $isPhoto = ($field === 'StudentPhoto' || $field === 'GradeConversionPhoto');
            $validMimes = $isPhoto ? $photoAllowedMimeTypes : $allowedMimeTypes;
            $validExts = $isPhoto ? $photoAllowedExtensions : $allowedExtensions;

            if (!in_array($ext, $validExts, true) || !in_array($mime, $validMimes, true)) {
                rollbackUploads($uploadedPaths);
                $allowedStr = implode(', ', $validExts);
                exitWithErrorJson("Invalid file type for {$field}. Allowed: {$allowedStr}.", 400);
            }

            $targetDir = $baseUploadDir . '/' . $folder;
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    rollbackUploads($uploadedPaths);
                    exitWithErrorJson("Failed to create upload directory for {$field}.", 500);
                }
            }

            $filename = uniqid('stu_') . '-' . time() . '.' . $ext;
            $targetPath = $targetDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                rollbackUploads($uploadedPaths);
                exitWithErrorJson("Failed to save uploaded file for {$field}.", 500);
            }

            // Store relative path to store in DB
            $relativePath = 'uploads/students/' . $folder . '/' . $filename;
            $uploadedPaths[] = $targetPath;
            $paths[$field] = $relativePath;
        }
    }

    return $paths;
}

function rollbackUploads(array $paths): void
{
    foreach ($paths as $path) {
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
        }
    }
}
