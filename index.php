<?php

define('APP_ROOT', __DIR__);

require_once APP_ROOT . "/@/lib/auth.php";
require_once APP_ROOT . "/@/lib/database.php";
require_once APP_ROOT . "/@/lib/env.php";
require_once APP_ROOT . "/@/lib/paths.php";
require_once APP_ROOT . "/@/lib/request.php";
require_once APP_ROOT . "/@/lib/response.php";
require_once APP_ROOT . "/@/lib/uploads.php";
require_once APP_ROOT . "/@/lib/utils.php";

function initEnv(): Env
{
  $devEnv = APP_ROOT . "/.env.development";
  $prodEnv = APP_ROOT . "/.env.production";
  $isDevEnv = file_exists($devEnv);
  $envPath = $isDevEnv ? $devEnv : $prodEnv;

  return Env::init($envPath, $isDevEnv);
}

function initSettings(Env $env)
{
  if ($env->isDevEnv) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('error_reporting', E_ALL);
  }

  date_default_timezone_set('Asia/Kolkata');
}

function initHeaders()
{
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: SAMEORIGIN');
  header('X-XSS-Protection: 1; mode=block');
  header('Referrer-Policy: strict-origin-when-cross-origin');
  header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
  header('Permissions-Policy: geolocation=none, microphone=none, camera=none');
  header('X-Permitted-Cross-Domain-Policies: none');
  header(
    "Content-Security-Policy: "
      // . "default-src 'self'; "
      // . "script-src 'self' 'unsafe-inline' https://accounts.google.com; "
      // . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
      . "font-src 'self' https://fonts.gstatic.com data:; "
      . "img-src 'self' data: blob: https://*.googleapis.com https://*.gstatic.com; "
      . "frame-src https://www.google.com https://accounts.google.com; "
      . "connect-src 'self' https://accounts.google.com https://oauth2.googleapis.com; "
      . "object-src 'none'; "
      . "base-uri 'self';"
  );
}

function initSession(Env $env)
{
  $sevenDays = 60 * 60 * 24 * 7;
  ini_set('session.gc_maxlifetime', $sevenDays);
  session_set_cookie_params($sevenDays, $env->baseUrl . "/", $env->domain, $env->secureCookies, $env->secureCookies);
  session_start();
}

function initDirs($dirs)
{
  foreach ($dirs as $dir) {
    $path = pathOf($dir);
    if (!is_dir($path)) {
      mkdir($path);
    }
  }
}

function initDatabase(Env $env)
{
  Database::init($env);
}

function navigate(string $url)
{
  $uri = rawurldecode(parse_url($url, PHP_URL_PATH));
  $uri = rtrim($uri, '/') ?: '/';

  $env = Env::getInstance();
  
  $baseDir = $env->baseDir;
  $altBaseDir = str_replace('-', ' ', $baseDir);

  if (stripos($uri, $baseDir) === 0) {
    $uri = substr($uri, strlen($baseDir)) ?: '/';
  } elseif (stripos($uri, $altBaseDir) === 0) {
    $uri = substr($uri, strlen($altBaseDir)) ?: '/';
  }

  $routeFile = APP_ROOT . $uri . '.php';
  $indexFile = APP_ROOT . $uri . '/index.php';

  $normUri = str_replace(' ', '-', $uri);
  $normRouteFile = APP_ROOT . $normUri . '.php';
  $normIndexFile = APP_ROOT . $normUri . '/index.php';

  if ($uri === "/" || $normUri === "/") {
    exitWithRedirect('/staff');
  } elseif (file_exists($routeFile)) {
    require $routeFile;
  } elseif (file_exists($indexFile)) {
    require $indexFile;
  } elseif (file_exists($normRouteFile)) {
    require $normRouteFile;
  } elseif (file_exists($normIndexFile)) {
    require $normIndexFile;
  } else {
    http_response_code(404);
    require APP_ROOT . "/404.php";
  }
}

$dirs = [
  "/@/cache",
  "/@/uploads",
  "/@/uploads/staff",
  '/@/uploads/brochures',
  '/@/uploads/fee-payments',
];

$env = initEnv();
initSettings($env);
initDatabase($env);
initSession($env);
initHeaders();
initDirs($dirs);

navigate($_SERVER['REQUEST_URI']);
