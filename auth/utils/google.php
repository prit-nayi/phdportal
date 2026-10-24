<?php

function getGoogleCerts(): array
{
  $cachePath = pathof('/@/cache/google_certs.json');
  $maxAge    = 6 * 60 * 60; // 6 hours

  if (file_exists($cachePath) && (time() - filemtime($cachePath)) < $maxAge) {
    $cached = json_decode(file_get_contents($cachePath), true);
    if (!empty($cached['keys'])) {
      return $cached;
    }
  }

  $fresh = file_get_contents('https://www.googleapis.com/oauth2/v3/certs');
  if ($fresh !== false) {
    file_put_contents($cachePath, $fresh, LOCK_EX);
    return json_decode($fresh, true);
  }

  // Fallback: use stale cache if network is unreachable
  if (file_exists($cachePath)) {
    return json_decode(file_get_contents($cachePath), true);
  }

  return [];
}

function verifyGoogleIdToken(string $idToken, string $clientId)
{
  $parts = explode('.', $idToken);
  if (count($parts) !== 3) {
    return false;
  }

  $header  = json_decode(base64url_decode($parts[0]), true);
  $payload = json_decode(base64url_decode($parts[1]), true);

  $keys = getGoogleCerts();
  $key  = null;

  foreach ($keys['keys'] as $k) {
    if ($k['kid'] === $header['kid']) {
      $key = $k;
      break;
    }
  }

  if (
    !$key ||
    $payload['iss'] !== 'https://accounts.google.com' && $payload['iss'] !== 'accounts.google.com' ||
    $payload['aud'] !== $clientId ||
    $payload['exp'] < time()
  ) {
    return false;
  }

  return $payload;
}

function base64url_decode(string $data)
{
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '='));
}
