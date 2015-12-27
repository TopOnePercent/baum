<?php

$db = getenv('DB');

if($db == 'travis') {
    $config = array(
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'baum_testing',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => ''
    );
}

if(!$db) {
    $config = array(
        'driver'    => 'sqlite',
        'database'  => ':memory:',
        'prefix'    => ''
    );
}

return $config;
