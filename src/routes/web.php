<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->group(['prefix' => 'avatars/{emailHash}',], function () use ($app) {
    $app->get('/', 'AvatarController@getImage');
    $app->post('/', 'AvatarController@newImage');
    $app->delete('/', 'AvatarController@deleteImage');
});

$app->group(['prefix' => 'confirmation'], function () use ($app) {
    $app->get('/{code}', ['uses' => 'AvatarController@confirmation', 'as' => 'confirmation']);
});

$app->get('avatars/images/{filename}', 'ImageController@getAvatar');
$app->get('tests/images/{filename}', 'ImageController@getTests');