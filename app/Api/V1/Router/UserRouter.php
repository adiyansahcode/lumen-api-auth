<?php

declare(strict_types=1);

$api->group(
    [
        'prefix' => 'user'
    ],
    function ($api) {
        $api->get('/', [
            'as' => 'user.index',
            'uses' => 'App\Api\V1\Controllers\UserController@index',
        ]);
        $api->get('/{uuid}', [
            'as' => 'user.show',
            'uses' => 'App\Api\V1\Controllers\UserController@show',
        ]);
        $api->post('/', [
            'middleware' => [
                'jsonValidation'
            ],
            'as' => 'user.create',
            'uses' => 'App\Api\V1\Controllers\UserController@create',
        ]);
        $api->put('/{uuid}', [
            'middleware' => [
                'jsonValidation'
            ],
            'as' => 'user.update',
            'uses' => 'App\Api\V1\Controllers\UserController@update',
        ]);
        $api->patch('/{uuid}', [
            'middleware' => [
                'jsonValidation'
            ],
            'as' => 'user.update',
            'uses' => 'App\Api\V1\Controllers\UserController@update',
        ]);
        $api->delete('/{uuid}', [
            'as' => 'user.delete',
            'uses' => 'App\Api\V1\Controllers\UserController@delete',
        ]);
    }
);
