<?php

use Baum\Tests\Basic\Models\BasicBaum;
use Faker\Generator as Faker;

$factory->define(BasicBaum::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
    ];
});

$factory->state(BasicBaum::class, 'root', function ($faker) {
    return [
        'name' => $faker->word,
    ];
});

$factory->afterCreatingState(BasicBaum::class, 'root', function ($root, $faker) {
    $root->children()->create(factory(BasicBaum::class)->raw());
    $root->children()->create(factory(BasicBaum::class)->raw());
    $root->children()->create(factory(BasicBaum::class)->raw());
});

//creditCardNumber

$factory->state(BasicBaum::class, 'card', function ($faker) {
    return [
        'name' => $faker->creditCardNumber,
    ];
});

$factory->afterCreatingState(BasicBaum::class, 'card', function ($root, $faker) {
    $root->children()->create(factory(BasicBaum::class)->raw());
    $root->children()->create(factory(BasicBaum::class)->raw());
    $root->children()->create(factory(BasicBaum::class)->raw());
});
