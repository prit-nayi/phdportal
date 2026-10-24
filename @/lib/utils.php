<?php

function generateSeoFriendlyName(string $url): string
{
  return str_replace(' ', '-', strtolower($url));
}
