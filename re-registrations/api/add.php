<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$requiredFields = ['studentId'];

if (!postHasAll($requiredFields)) {
  exitWithErrorJson('Missing required fields.', 400);
}

$studentId = (int) trim($_POST['studentId']);
$reason    = isset($_POST['reason']) ? trim($_POST['reason']) : null;

if ($studentId <= 0) {
  exitWithErrorJson('Please select a student.', 422);
}

// Store empty reason as NULL
if ($reason === '') {
  $reason = null;
}

$db = Database::getInstance();

// Validate student exists and is active
$student = $db->get(
  "SELECT `id`, `isActive`, `isCancelled`, `isTransferred`
   FROM `phdStudents` WHERE `id` = ?",
  [$studentId]
);

if (!$student) {
  exitWithErrorJson('Selected student does not exist.', 422);
}

if (!$student->isActive) {
  exitWithErrorJson('Cannot re-register an inactive student.', 422);
}

if ($student->isCancelled) {
  exitWithErrorJson('Cannot re-register a cancelled student.', 422);
}

if ($student->isTransferred) {
  exitWithErrorJson('Cannot re-register a transferred student.', 422);
}

try {
  $db->execute(
    "INSERT INTO `phdReRegistrations` (`studentId`, `reason`) VALUES (?, ?)",
    [$studentId, $reason]
  );
  exitWithSuccessJson('Re-registration recorded successfully.');
} catch (\Exception $e) {
  exitWithErrorJson('Failed to record re-registration.', 500);
}
