<?php

declare(strict_types=1);

$site_machine_name = 'primary';
$site_path_fragment = 'primary.ddev.site';
$config_directory_name = 'primary';
$database_name = getenv('DRUPAL_DB_PRIMARY') ?: 'primary_db';

require dirname(__DIR__) . '/settings.shared.php';
