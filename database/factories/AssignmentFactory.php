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

$factory->define(App\Models\Assignment::class, function (Faker $faker) {
	return [
		'id' => $faker->uuid,
        'class_id' => '',
		'title' => $faker->word(6),
		'description' => $faker->text,
	];
});
