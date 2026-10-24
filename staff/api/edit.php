<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$fields = [
  'name',
  'emailAddress',
  'designation',
];

if (!getHasAll(['id']) || !postHasAll($fields)) {
  exitWithError(400);
}

$id = get('id');
$name = post('name');
$staffType = post('staffType');
$emailAddress = post('emailAddress');
$designation = post('designation');
$qualification = post('qualification');
$experience = post('experience');
$specialization = post('specialization');
$researchArea = post('researchArea');
$briefDescription = post('briefDescription');
$websiteLink = post('websiteLink');
$googleScholarLink = post('googleScholarLink');
$linkedinLink = post('linkedinLink');
$researchgateLink = post('researchgateLink');
$orcidLink = post('orcidLink');
$scopusLink = post('scopusLink');
$image = $_FILES['image'] ?? null;

$db = Database::getInstance();
$record = $db->get("SELECT * FROM `staff` WHERE `id` = ?", [$id]);
if (!$record) {
  exitWithError(404);
}

if (!$image) {
  $query = "UPDATE `staff` SET `name` = ?, `staffType` = ?, `emailAddress` = ?, `designation` = ?, `qualification` = ?, `experience` = ?, `specialization` = ?, `researchArea` = ?, `briefDescription` = ?, `websiteLink` = ?, `googleScholarLink` = ?, `linkedinLink` = ?, `researchgateLink` = ?, `orcidLink` = ?, `scopusLink` = ? WHERE `id` = ?";
  $params = [$name, $staffType, $emailAddress, $designation, $qualification, $experience, $specialization, $researchArea, $briefDescription, $websiteLink, $googleScholarLink, $linkedinLink, $researchgateLink, $orcidLink, $scopusLink, $id];

  $db->execute($query, $params);
  exit();
}

$uploadDir = pathOf('/@/uploads/staff');
$oldImagePath = "$uploadDir/{$record->imageFileName}";

if (file_exists($oldImagePath) && $record->imageFileName != null) {
  unlink($oldImagePath);
}
$imageName = uploadFileWithTimestampAsName($image, $uploadDir);

$query = "UPDATE `staff` SET `name` = ?, `staffType` = ?, `emailAddress` = ?, `designation` = ?, `qualification` = ?, `experience` = ?, `specialization` = ?, `researchArea` = ?, `briefDescription` = ?, `websiteLink` = ?, `googleScholarLink` = ?, `linkedinLink` = ?, `researchgateLink` = ?, `orcidLink` = ?, `scopusLink` = ?, `imageFileName` = ? WHERE `id` = ?";
$params = [$name, $staffType, $emailAddress, $designation, $qualification, $experience, $specialization, $researchArea, $briefDescription, $websiteLink, $googleScholarLink, $linkedinLink, $researchgateLink, $orcidLink, $scopusLink, $imageName, $id];

$db->execute($query, $params);
