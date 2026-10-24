<?php

function exitWithSuccess(array | null $data = null)
{
  http_response_code(200);

  if (!is_null($data)) {
    header('Content-Type: application/json');
    echo json_encode($data);
  }

  exit();
}

function exitWithSuccessJson(string $message, int $statusCode = 200)
{
  http_response_code($statusCode);
  header('Content-Type: application/json');
  echo json_encode(['message' => $message]);
  exit();
}

function exitWithErrorJson(string $message, int $statusCode = 500)
{
  http_response_code($statusCode);
  header('Content-Type: application/json');
  echo json_encode(['message' => $message]);
  exit();
}

function exitWithError(int $statusCode)
{
  http_response_code($statusCode);
  exit();
}

function exitWithRedirect(string $path)
{
  header("Location: " . urlOf($path));
  exit();
}
