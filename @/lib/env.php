<?php

class Env
{
  private static Env|null $instance = null;

  public readonly string $baseUrl;
  public readonly string $baseDir;
  public readonly string $domain;
  public readonly bool $secureCookies;
  public readonly string $googleClientId;
  public readonly string $dbDsn;
  public readonly string $dbUser;
  public readonly string $dbPassword;
  public readonly bool $isDevEnv;

  public static function init(string $path, bool $isDevEnv): Env
  {
    if (self::$instance == null) {
      self::$instance = new self($path, $isDevEnv);
    }

    return self::$instance;
  }

  public static function getInstance(): Env
  {
    return self::$instance;
  }

  private static function getKeyValuePairs(string $filePath): array
  {
    $lines = file($filePath);
    $nonEmptyLines = array_filter($lines, fn($line) => trim($line) !== '');

    $vars = [];
    foreach ($nonEmptyLines as $line) {
      $parts = explode('=', $line, 2);
      $key = trim($parts[0]);
      $value = trim($parts[1]);
      $vars[$key] = $value;
    }

    return $vars;
  }

  private function __construct(string $filePath, bool $isDevEnv)
  {
    $vars = self::getKeyValuePairs($filePath);

    $this->isDevEnv = $isDevEnv;
    $this->baseUrl = $vars['BASE_URL'];
    $this->baseDir = $vars['BASE_DIR'];
    $this->domain = $vars['DOMAIN'];
    $this->secureCookies = $vars['SECURE_COOKIES'];
    $this->googleClientId = $vars['GOOGLE_CLIENT_ID'];
    $this->dbDsn = $vars['DB_DSN'];
    $this->dbUser = $vars['DB_USER'];
    $this->dbPassword = $vars['DB_PASSWORD'];
  }
}
