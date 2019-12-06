<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\Gruter\ResourceViewer\Tests\Fixtures\Ticket::class, function (Faker $faker) {
    static $password;

    return [
        'user_id' => factory(\Gruter\ResourceViewer\Tests\Fixtures\TestUser::class),
        'category_id' => factory(\Gruter\ResourceViewer\Tests\Fixtures\Category::class),
        'subject' => $faker->name,
    ];
});
