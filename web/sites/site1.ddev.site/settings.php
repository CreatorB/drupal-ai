<?php

declare(strict_types=1);

$site_machine_name = 'site1';
$site_path_fragment = 'site1.ddev.site';
$config_directory_name = 'site1';
$database_name = getenv('DRUPAL_DB_SITE1') ?: 'site1_db';

require dirname(__DIR__) . '/settings.shared.php';
