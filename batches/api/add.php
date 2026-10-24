<?php

postOrExit();

Authorize::forRolesOrExit([Role::ADMIN]);

$fields = ['name', 'enrollmentYear', 'feeAmount'];

if (!postHasAll($fields)) {
    exitWithErrorJson('Missing required fields.', 400);
}

$name = trim(post('name'));
$enrollmentYear = trim(post('enrollmentYear'));
$feeAmount = trim(post('feeAmount'));

if ($name === '') {
    exitWithErrorJson('Batch Name is required.', 422);
}

if ($enrollmentYear === '') {
    exitWithErrorJson('Enrollment Year is required.', 422);
}

if ($feeAmount === '') {
    exitWithErrorJson('Fee Amount is required.', 422);
}

$db = Database::getInstance();

$db->execute(
    "INSERT INTO `phdBatches` (`name`, `enrollmentYear`, `feeAmount`) VALUES (?, ?, ?)",
    [$name, $enrollmentYear, $feeAmount]
);

exitWithSuccessJson('Batch added successfully.');