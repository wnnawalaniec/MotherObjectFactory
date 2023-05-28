#!/usr/bin/env php
<?php
declare(strict_types=1);

use MotherOfAllObjects\Command\GenerateMotherObjectCommand;
use Symfony\Component\Console\Application;

if (file_exists($vendor = __DIR__ . '/../vendor')) {
    define('ROOT_DIR', __DIR__ . '/..');
} elseif (file_exists($vendor = __DIR__ . '/../../../vendor')) {
    define('ROOT_DIR', __DIR__ . '/../..');
}

include_once $vendor . '/autoload.php';

define('PROJECT_ROOT_DIR', $vendor . '/..');

$application = new Application();
$application->add(new GenerateMotherObjectCommand());
$application->run();