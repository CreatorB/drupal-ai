<?php

declare(strict_types=1);

$site_machine_name = 'site2';
$site_path_fragment = 'site2.ddev.site';
$config_directory_name = 'site2';
$database_name = getenv('DRUPAL_DB_SITE2') ?: 'site2_db';

require dirname(__DIR__) . '/settings.shared.php';
