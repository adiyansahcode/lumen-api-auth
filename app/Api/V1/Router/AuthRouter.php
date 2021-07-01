<?php

declare(strict_types=1);

$api->group(
    [
        'prefix' => 'auth'
    ],
    function ($api) {
        $api->group(
            [
                'prefix' => 'token'
            ],
            function ($api) {
                $api->get('/', [
                    'as' => 'auth.token.register',
                    'uses' => 'App\Api\V1\Controllers\AuthController@tokenRegister',
                ]);
                $api->post('/', [
                    'middleware' => [
                        'jsonValidation'
                    ],
                    'as' => 'auth.token.create',
                    'uses' => 'App\Api\V1\Controllers\AuthController@tokenCreate',
                ]);
                $api->put('/', [
                    'middleware' => [
                        'api.auth'
                    ],
                    'as' => 'auth.token.refresh',
                    'uses' => 'App\Api\V1\Controllers\AuthController@tokenRefresh',
                ]);
                $api->delete('/', [
                    'middleware' => [
                        'api.auth'
                    ],
                    'as' => 'auth.token.delete',
                    'uses' => 'App\Api\V1\Controllers\AuthController@tokenDelete',
                ]);
            }
        );

        $api->post('/', [
            'middleware' => [
                'jsonValidation'
            ],
            'as' => 'auth.register',
            'uses' => 'App\Api\V1\Controllers\AuthController@register',
        ]);

        $api->get('/', [
            'middleware' => [
                'api.auth'
            ],
            'as' => 'auth.profile',
            'uses' => 'App\Api\V1\Controllers\AuthController@profile',
        ]);

        $api->put('/', [
            'middleware' => [
                'api.auth',
                'jsonValidation'
            ],
            'as' => 'auth.profile.update',
            'uses' => 'App\Api\V1\Controllers\AuthController@profileUpdate',
        ]);

        $api->patch('/', [
            'middleware' => [
                'api.auth',
                'jsonValidation'
            ],
            'as' => 'auth.profile.update',
            'uses' => 'App\Api\V1\Controllers\AuthController@profileUpdate',
        ]);

        $api->post('/photo', [
            'middleware' => [
                'api.auth',
            ],
            'as' => 'auth.photo.upload',
            'uses' => 'App\Api\V1\Controllers\AuthController@photoUpload',
        ]);
    }
);
