<?php

function pathOf(string $path): string
{
  $env = Env::getInstance();
  return $_SERVER['DOCUMENT_ROOT'] . $env->baseDir . "/" . ltrim($path, "/");
}

function urlOf(string $path): string
{
  $env = Env::getInstance();
  return $env->baseUrl . '/' . ltrim($path, "/");
}

function isUrlActive(string $path): string
{
  $currentPath = rtrim(rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');
  $normCurrentPath = str_replace(' ', '-', $currentPath);
  $normPath = str_replace(' ', '-', $path);
  return ($currentPath === $path || $normCurrentPath === $normPath) ? 'active' : '';
}
