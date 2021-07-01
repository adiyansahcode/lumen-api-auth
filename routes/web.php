<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return Response()
        ->json('OK', 200);
});

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    $api->group(['prefix' => 'v1'], function ($api) {
        $api->get('/', function () {
            return Response()
                ->json('OK', 200)
                ->header('Content-Type', 'application/json');
        });

        /**
         * read all file router
         */
        foreach (glob(realpath(app()->path() . '/Api/V1/Router') . "/*Router.php") as $filename) {
            include $filename;
        }
    });
});
