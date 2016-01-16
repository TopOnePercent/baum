<?php

/**
 * Register composer auto  loader.
 */
require __DIR__.'/vendor/autoload.php';

/*
 * Initialize Capsule
 */
$config = require __DIR__.'/tests/config/database.php';

$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($config);
$capsule->setEventDispatcher(new Illuminate\Events\Dispatcher(new Illuminate\Container\Container));
$capsule->bootEloquent();
$capsule->setAsGlobal();

/*
 * Autoload required libraries
 */
$__autoload_paths = ['models', 'migrators', 'seeders'];

foreach ($__autoload_paths as $path) {
    foreach (glob(__DIR__."/tests/$path/*.php") as $dep) {
        require_once $dep;
    }
}

/**
 * Require test helpers.
 */
require __DIR__.'/tests/suite/support.php';
require __DIR__.'/tests/suite/BaumTestCase.php';
require __DIR__.'/tests/suite/CategoryTestCase.php';
require __DIR__.'/tests/suite/ClusterTestCase.php';
