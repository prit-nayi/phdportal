<?php
postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

if (!postHasAll(['id'])) exitWithErrorJson("ID is required.", 400);

$db = Database::getInstance();

$id = post('id');
$fullName = post('fullName');
$designation = post('designation');
$organization = post('organization');
$mobileNumber = post('mobileNumber');
$emailAdderess = post('emailAdderess');

if (empty(trim($fullName))) exitWithErrorJson("Full name is required.", 400);
if (empty(trim($designation))) exitWithErrorJson("Designation is required.", 400);
if (empty(trim($organization))) exitWithErrorJson("Organization is required.", 400);
if (empty(trim($mobileNumber)) || !preg_match('/^\d{10}$/', trim($mobileNumber))) exitWithErrorJson("Valid 10-digit mobile number is required.", 400);
if (empty(trim($emailAdderess)) || !filter_var(trim($emailAdderess), FILTER_VALIDATE_EMAIL)) exitWithErrorJson("Valid email address is required.", 400);

$db->execute(
  "UPDATE `externalPanelMembers` SET `fullName` = ?, `designation` = ?, `organization` = ?, `mobileNumber` = ?, `emailAdderess` = ? WHERE `id` = ?",
  [trim($fullName), trim($designation), trim($organization), trim($mobileNumber), trim($emailAdderess), $id]
);

exitWithSuccess();
