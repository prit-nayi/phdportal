<?php

/**
 * Handles proposal presentation file upload for the admission exam results module.
 * Returns the relative path string on success, null if no file was uploaded, or exits with JSON error on failure.
 *
 * @param array $uploadedPaths Reference array to track created files for rollback on error.
 * @return string|null Relative file path or null if no file uploaded.
 */
function processProposalPresentationUpload(array &$uploadedPaths): ?string
{
    if (!isset($_FILES['proposalPresentationFile']) || $_FILES['proposalPresentationFile']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES['proposalPresentationFile'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        rollbackUploads($uploadedPaths);
        exitWithErrorJson('Error uploading proposal presentation file.', 400);
    }

    $maxSize = 10 * 1024 * 1024; // 10 MB limit
    if ($file['size'] > $maxSize) {
        rollbackUploads($uploadedPaths);
        exitWithErrorJson('Proposal presentation file exceeds the 10MB size limit.', 400);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'ppt', 'pptx', 'doc', 'docx'];

    if (!in_array($ext, $allowedExtensions, true)) {
        rollbackUploads($uploadedPaths);
        exitWithErrorJson('Invalid file format. Allowed formats: .pdf, .ppt, .pptx, .doc, .docx', 400);
    }

    $baseUploadDir = pathOf('/uploads/admission_exam_results');
    if (!is_dir($baseUploadDir)) {
        if (!mkdir($baseUploadDir, 0777, true)) {
            rollbackUploads($uploadedPaths);
            exitWithErrorJson('Failed to create upload directory.', 500);
        }
    }

    $filename = 'presentation_' . uniqid() . '_' . time() . '.' . $ext;
    $targetPath = $baseUploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        rollbackUploads($uploadedPaths);
        exitWithErrorJson('Failed to save uploaded presentation file.', 500);
    }

    $relativePath = 'uploads/admission_exam_results/' . $filename;
    $uploadedPaths[] = $targetPath;

    return $relativePath;
}

/**
 * Clean up uploaded files on failure.
 *
 * @param array $paths Array of absolute file paths to delete.
 */
function rollbackUploads(array $paths): void
{
    foreach ($paths as $path) {
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
        }
    }
}
