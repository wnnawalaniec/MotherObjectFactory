#!/usr/bin/env php
<?php
declare(strict_types=1);

use Symfony\Component\Console\Application;

define('COMPOSER_BIN_PATH', $_composer_bin_dir ?? __DIR__);
define('COMPOSER_AUTOLOAD_PATH', $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php');
require COMPOSER_AUTOLOAD_PATH;

$application = new Application();
$application->run();