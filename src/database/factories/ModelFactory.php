<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Avatar::class, function (Faker\Generator $faker) {
    $email = $faker->unique()->safeEmail;
    $emailHash = md5($email);
    $img = $faker->numberBetween(1, 5) . $faker->randomElement(['.jpg', '.gif', '.bmp', '.png']);
    return [
        'email_hash' => $emailHash,
        'email' => $email,
        'image_file' => $img,
    ];
});

$factory->define(App\AvatarOperation::class, function (Faker\Generator $faker) {
    $avatar = App\Avatar::all()->random();
    $img = $faker->numberBetween(1, 5) . $faker->randomElement(['.jpg', '.gif', '.bmp', '.png']);
    $method = $faker->randomElement(array_keys(\App\AvatarOperation::$methods));
    return [
        'email_hash' => $avatar->email_hash,
        'method' => $method,
        'image_file' => $img,
        'code' => \App\AvatarOperation::generateCode()
    ];
});
