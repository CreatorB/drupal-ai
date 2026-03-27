<?php

declare(strict_types=1);

if (!isset($site_machine_name, $site_path_fragment, $config_directory_name, $database_name)) {
  throw new RuntimeException('Site settings must define site variables before loading settings.shared.php.');
}

$project_root = dirname(__DIR__, 2);

$env_file = $project_root . '/.env';
if (is_readable($env_file)) {
  $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
  foreach ($lines as $line) {
    $trimmed = trim($line);
    if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
      continue;
    }

    [$name, $value] = explode('=', $trimmed, 2);
    $name = trim($name);
    $value = trim($value);

    if ($name === '' || getenv($name) !== false) {
      continue;
    }

    if (
      (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
      (str_starts_with($value, "'") && str_ends_with($value, "'"))
    ) {
      $value = substr($value, 1, -1);
    }

    putenv($name . '=' . $value);
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
  }
}

$databases['default']['default'] = [
  'database' => $database_name,
  'username' => getenv('DRUPAL_DB_USER') ?: 'db',
  'password' => getenv('DRUPAL_DB_PASSWORD') ?: 'db',
  'prefix' => '',
  'host' => getenv('DRUPAL_DB_HOST') ?: 'db',
  'port' => getenv('DRUPAL_DB_PORT') ?: '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];

$settings['hash_salt'] = hash('sha256', $site_machine_name . '-fxmedia-drupal-ai');
$settings['config_sync_directory'] = $project_root . '/config/' . $config_directory_name;
$settings['file_public_path'] = 'sites/' . $site_path_fragment . '/files';
$settings['rebuild_access'] = FALSE;
$settings['skip_permissions_hardening'] = TRUE;

$settings['trusted_host_patterns'] = [
  '^primary\\.ddev\\.site$',
  '^site1\\.ddev\\.site$',
  '^site2\\.ddev\\.site$',
  '^localhost$',
  '^.+\\.clientname\\.com$',
  '^.+\\.trycloudflare\\.com$',
  '^.+\\.ngrok-free\\.app$',
];

$config['drupal_ai_search.settings']['opencode_api_key'] = getenv('OPENCODE_API_KEY') ?: '';
$config['drupal_ai_search.settings']['opencode_endpoint'] = getenv('OPENCODE_API_ENDPOINT') ?: 'https://api.opencode.dev/v1';
$config['drupal_ai_search.settings']['opencode_model'] = getenv('OPENCODE_MODEL') ?: 'minimax-m2.5-free';
$config['drupal_ai_search.settings']['opencode_fallback_model'] = getenv('OPENCODE_FALLBACK_MODEL') ?: 'mimo-v2-pro-free';
$config['drupal_ai_search.settings']['provider_name'] = getenv('AI_PROVIDER_NAME') ?: 'OpenCode';
$config['drupal_ai_search.settings']['provider_site_url'] = getenv('AI_PROVIDER_SITE_URL') ?: 'https://primary.ddev.site';
$config['drupal_ai_search.settings']['provider_app_name'] = getenv('AI_PROVIDER_APP_NAME') ?: 'Drupal AI Multisite Demo';
$config['drupal_ai_search.settings']['libretranslate_endpoint'] = getenv('LIBRETRANSLATE_ENDPOINT') ?: 'http://localhost:5000';

if (file_exists(__DIR__ . '/default/default.services.yml')) {
  $settings['container_yamls'][] = __DIR__ . '/default/default.services.yml';
}

if (file_exists(__DIR__ . '/' . $site_path_fragment . '/settings.local.php')) {
  require __DIR__ . '/' . $site_path_fragment . '/settings.local.php';
}
