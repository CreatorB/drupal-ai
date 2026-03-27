<?php

/**
 * Domain-based multisite mapping.
 */

$sites['primary.ddev.site'] = 'primary.ddev.site';
$sites['site1.ddev.site'] = 'site1.ddev.site';
$sites['site2.ddev.site'] = 'site2.ddev.site';

$sites['primary.example.org'] = 'primary.ddev.site';
$sites['www.primary.example.org'] = 'primary.ddev.site';
$sites['site1.example.org'] = 'site1.ddev.site';
$sites['www.site1.example.org'] = 'site1.ddev.site';
$sites['site2.example.org'] = 'site2.ddev.site';
$sites['www.site2.example.org'] = 'site2.ddev.site';

$sites['drupal-ai.ddev.site'] = 'primary.ddev.site';
$sites['localhost'] = 'primary.ddev.site';

// Tunnel providers: read mapping from tunnel-sites.json if available.
$tunnel_map_file = dirname(__DIR__, 2) . '/tunnel-sites.json';
$host = $_SERVER['HTTP_HOST'] ?? '';

if ($host && is_readable($tunnel_map_file)) {
  $tunnel_map = json_decode(file_get_contents($tunnel_map_file), TRUE) ?: [];
  if (isset($tunnel_map[$host])) {
    $sites[$host] = $tunnel_map[$host];
  }
}

// Fallback: unmatched tunnel hostnames go to primary.
if ($host && !isset($sites[$host])) {
  $tunnel_suffixes = ['.trycloudflare.com', '.ngrok-free.app'];
  foreach ($tunnel_suffixes as $suffix) {
    if (str_ends_with($host, $suffix)) {
      $sites[$host] = 'primary.ddev.site';
      break;
    }
  }
}
