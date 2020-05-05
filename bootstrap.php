<?php

require __DIR__.'/vendor/autoload.php';

// Boot Eloquent, we configure the database in BaumTestCase::getEnvironmentSetUp
$capsule = new Illuminate\Database\Capsule\Manager();
$capsule->setEventDispatcher(new Illuminate\Events\Dispatcher(new Illuminate\Container\Container()));
$capsule->bootEloquent();
$capsule->setAsGlobal();

// Autoload required classes
$paths = ['models', 'migrators', 'seeders'];

foreach ($paths as $path) {
    foreach (glob(__DIR__."/tests/$path/*.php") as $dep) {
        require_once $dep;
    }
}

// Helpers / base classes
require __DIR__.'/tests/suite/support.php';
require __DIR__.'/tests/suite/BaumTestCase.php';
require __DIR__.'/tests/suite/CategoryTestCase.php';
require __DIR__.'/tests/suite/OrderedCategoryTestCase.php';
require __DIR__.'/tests/suite/ClusterTestCase.php';
require __DIR__.'/tests/suite/OrderedClusterTestCase.php';
