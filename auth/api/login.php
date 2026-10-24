<?php

require_once(pathOf("/auth/utils/google.php"));
postOrExit();

$env = Env::getInstance();
$input = file_get_contents('php://input');
$json = json_decode($input);

$googleToken = $json->googleToken;
$clientId = $env->googleClientId;

$userData = verifyGoogleIdToken($googleToken, $clientId);
if (!$userData) {
  exitWithError(401);
}

$name = $userData['name'];
$email = $userData['email'];
$picture = $userData['picture'];

try {
  $db = Database::getInstance();
  $user = $db->get("SELECT `id`, `role`, `isActive` FROM `users` WHERE `gmail` = ?", [$email]);

  if (!$user || !$user->isActive) {
    exitWithErrorJson("You are not permitted to use the placement portal.", 401);
  }

  $name = "";
  if ($user->role == "Admin") {
    $userInfo = $db->get("SELECT `name` FROM `admins` WHERE `userId` = ?", [$user->id]);
    $name = $userInfo->name;
  } else if ($user->role == "Student") {
    $userInfo = $db->get("SELECT `firstName` FROM `students` WHERE `userId` = ?", [$user->id]);
    $name = $userInfo->firstName;
  }

  $_SESSION['id'] = $user->id;
  $_SESSION['name'] = $name;
  $_SESSION['role'] = Role::from($user->role);
} catch (Exception $ex) {
  exitWithError($ex->getMessage());
}
