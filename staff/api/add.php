<?php

postOrExit();
Authorize::forRolesOrExit([Role::ADMIN]);

$fields = [
  'name',
  'emailAddress',
  'designation',
];

if (!postHasAll($fields)) {
  exitWithError(400);
}

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

$imageName = $image ? uploadFileWithTimestampAsName($image, pathOf('/@/uploads/staff')) : null;
$db = Database::getInstance();

$lastIndexStaff = $db->get("SELECT MAX(`orderIndex`) AS `lastIndex` FROM `staff`");
$lastOrderIndex = ($lastIndexStaff->lastIndex ?? 0) + 1;

$query = "INSERT INTO `staff` (`name`, `staffType`, `orderIndex`, `emailAddress`, `designation`, `qualification`, `experience`, `specialization`, `researchArea`, `briefDescription`, `websiteLink`, `googleScholarLink`, `linkedinLink`, `researchgateLink`, `orcidLink`, `scopusLink`, `imageFileName`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$params = [$name, $staffType, $lastOrderIndex, $emailAddress, $designation, $qualification, $experience, $specialization, $researchArea, $briefDescription, $websiteLink, $googleScholarLink, $linkedinLink, $researchgateLink, $orcidLink, $scopusLink, $imageName];

$db->execute($query, $params);
