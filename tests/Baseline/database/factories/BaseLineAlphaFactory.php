<?php

use Faker\Generator as Faker;
use Baum\Tests\Baseline\Models\BaseLineAlpha;


$factory->define(BaseLineAlpha::class, function (Faker $faker) {
    return [
        'value' => $faker->sentence(6)
    ];
});
