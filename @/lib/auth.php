<?php

enum Role: string
{
  case ADMIN = "Admin";
  case STUDENT = "Student";
}

class Authorize
{
  private static function _forRoles(array $roles = []): bool
  {
    $userRole = $_SESSION['role'] ?? null;
    if ($userRole === null || !in_array($userRole, $roles)) {
      return false;
    }
    return true;
  }

  private static function _forAnyRole(): bool
  {
    $userRole = $_SESSION['role'] ?? null;

    if ($userRole === null) {
      return false;
    }
    return true;
  }

  public static function forRolesOrRedirect(array $roles = [])
  {
    if (!self::_forRoles($roles)) {
      exitWithRedirect("/auth/login");
    }
  }

  public static function forRolesOrExit(array $roles = [], $httpStatusCode = 403)
  {
    if (!self::_forRoles($roles)) {
      http_response_code($httpStatusCode);
      exit();
    }
  }

  public static function forAnyRoleOrRedirect()
  {
    if (!self::_forAnyRole()) {
      exitWithRedirect("/auth/login");
    }
  }

  public static function forAnyRoleOrExit($httpStatusCode = 403)
  {
    if (!self::_forAnyRole()) {
      http_response_code($httpStatusCode);
      exit();
    }
  }

  public static function forAnonymousOrRedirect()
  {
    if (self::_forAnyRole()) {
      exitWithRedirect("/");
    }
  }

  public static function forAnonymousOrExit($httpStatusCode = 403)
  {
    if (self::_forAnyRole()) {
      http_response_code($httpStatusCode);
      exit();
    }
  }
}
