<?php

function getOrRedirect(string $url = '/')
{
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header("Location: " . urlOf($url));
    exit();
  }
}

function getOrExit(int $httpStatusCode = 405)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code($httpStatusCode);
    exit();
  }
}

function postOrExit(int $httpStatusCode = 405)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code($httpStatusCode);
    exit();
  }
}

function postOrRedirect(string $url = '/')
{
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . urlOf($url));
    exit();
  }
}

function deleteOrExit(int $httpStatusCode = 405)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code($httpStatusCode);
    exit();
  }
}

function getHasAll(array $fields): bool
{
  return array_reduce($fields, fn($carry, $field) => $carry && isset($_GET[$field]), true);
}

function postHasAll(array $fields): bool
{
  return array_reduce($fields, fn($carry, $field) => $carry && isset($_POST[$field]), true);
}

function requestHasAll(array $fields): bool
{
  return array_reduce($fields, fn($carry, $field) => $carry && isset($_REQUEST[$field]), true);
}

function filesHasAll(array $fields): bool
{
  return array_reduce($fields, fn($carry, $field) => $carry && isset($_FILES[$field]), true);
}

function get(string $key, string | null $default = null): mixed
{
  return htmlspecialchars($_GET[$key] ?? $default);
}

function post(string $key, string | null $default = null, bool $sanitize = true): mixed
{
  if (!isset($_POST[$key])) {
    return $default;
  }

  if (!$sanitize) {
    return $_POST[$key];
  }

  return htmlspecialchars($_POST[$key] ?? $default);
}

function request(string $key, string | null $default = null): mixed
{
  return htmlspecialchars($_REQUEST[$key] ?? $default);
}

function files(string $key, string | null $default = null): mixed
{
  return $_FILES[$key] ?? $default;
}
