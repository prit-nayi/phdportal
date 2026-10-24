<?php

deleteOrExit();

Authorize::forRolesOrExit([Role::ADMIN]);

if (!getHasAll(['id'])) {
    exitWithErrorJson('Missing batch ID.', 400);
}

$id = (int) get('id');

$db = Database::getInstance();

$record = $db->get(
    "SELECT `id` FROM `phdBatches` WHERE `id` = ?",
    [$id]
);

if (!$record) {
    exitWithErrorJson('Batch not found.', 404);
}

$studentCount = $db->get(
    "SELECT COUNT(*) AS `total` FROM `phdStudents` WHERE `batchId` = ?",
    [$id]
);

if ($studentCount && (int) $studentCount->total > 0) {
    exitWithErrorJson(
        "Cannot delete this batch. {$studentCount->total} student(s) are assigned to it. Please reassign or remove them first.",
        409
    );
}

$db->execute(
    "DELETE FROM `phdBatches` WHERE `id` = ?",
    [$id]
);

exitWithSuccessJson('Batch deleted successfully.');