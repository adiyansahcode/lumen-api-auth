<?php

declare(strict_types=1);

namespace App\Api\V1\Validations;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthValidation
{
    public function tokenRegister(object $request, string $type): void
    {
        $validator = Validator::make($request->all(), [
            'uuid' => [
                'required',
                'filled',
                'string',
                'uuid',
            ],
            'time' => [
                'required',
                'filled',
                'numeric',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->toArray();
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        }

        $validator = Validator::make($request->all(), [
            'uuid' => [
                'exists:App\Models\User,uuid',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '422',
                'code' => '422',
                'title' => 'invalid request',
                'detail' => 'uuid resource is not exists.',
                'source' => ['parameter' => 'uuid'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        }

        $time = Carbon::createFromTimestamp($request->time)->toDateTimeString();
        if (Carbon::now()->gte($time)) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '422',
                'code' => '422',
                'title' => 'invalid request',
                'detail' => 'time expired',
                'source' => ['parameter' => 'time'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        }
    }

    public function tokenCreate(object $request, string $type): void
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'filled',
                'string',
            ],
            'password' => [
                'required',
                'filled',
                'string',
            ],
            'isRemember' => [
                'required',
                'filled',
                'numeric',
                Rule::in([0, 1]),
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->toArray();
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        }
    }

    public function registerValidation(object $request, string $type): void
    {
        // validate data
        $validator = Validator::make($request->json()->all(), [
            'data' => [
                'required',
                'array',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'invalid request',
                'detail' => "missing 'data' parameter at request.",
                'source' => ['pointer' => ''],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        // validate attributes
        $validator = Validator::make($request->json('data'), [
            'attributes' => [
                'required',
                'array',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'invalid request',
                'detail' => "missing 'attributes' parameter at request.",
                'source' => ['pointer' => ''],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        // validate data type
        $validator = Validator::make($request->json('data'), [
            'type' => [
                'required',
                'filled',
                'string',
                Rule::in([$type]),
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'invalid request',
                'detail' => 'type resource is invalid.',
                'source' => ['pointer' => 'data/type'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        $validator = Validator::make($request->json('data'), [
            'id' => [
                'required',
                'filled',
                'string',
                'uuid',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'invalid request',
                'detail' => 'id resource is invalid format.',
                'source' => ['pointer' => 'data/id'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        $validator = Validator::make($request->json('data'), [
            'id' => [
                'unique:App\Models\User,uuid'
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'invalid request',
                'detail' => 'id resource is not exists.',
                'source' => ['pointer' => 'data/id'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        $validator = Validator::make($request->json('data.attributes'), [
            'fullname' => [
                'required',
                'filled',
                'string',
                'between:2,100'
            ],
            'username' => [
                'required',
                'filled',
                'string',
                'unique:App\Models\User,username',
                'max:50'
            ],
            'email' => [
                'required',
                'filled',
                'string',
                'email',
                'unique:App\Models\User,email',
                'max:50'
            ],
            'phone' => [
                'required',
                'filled',
                'string',
                'unique:App\Models\User,phone',
                'max:50'
            ],
            'password' => [
                'required',
                'filled',
                'string',
                'min:5'
            ],
            'passwordConfirm' => [
                'required',
                'filled',
                'string',
                'same:password'
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->toArray();
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        }
    }

    public function profileUpdateValidation(object $request, string $type): void
    {
        // validate data
        $validator = Validator::make($request->json()->all(), [
            'data' => [
                'required',
                'array',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'invalid request',
                'detail' => "missing 'data' parameter at request.",
                'source' => ['pointer' => ''],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        // validate attributes
        $validator = Validator::make($request->json('data'), [
            'attributes' => [
                'required',
                'array',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'invalid request',
                'detail' => "missing 'attributes' parameter at request.",
                'source' => ['pointer' => ''],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        // validate data type
        $validator = Validator::make($request->json('data'), [
            'type' => [
                'required',
                'filled',
                'string',
                Rule::in([$type]),
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'invalid request',
                'detail' => 'type resource is invalid.',
                'source' => ['pointer' => 'data/type'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        $validator = Validator::make($request->json('data'), [
            'id' => [
                'required',
                'filled',
                'string',
                'uuid',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'invalid request',
                'detail' => 'id resource is invalid format.',
                'source' => ['pointer' => 'data/id'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        $validator = Validator::make($request->json('data'), [
            'id' => [
                'exists:App\Models\User,uuid',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '422',
                'code' => '422',
                'title' => 'invalid request',
                'detail' => 'id resource is not exists.',
                'source' => ['pointer' => 'data/id'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        }

        $dataDb = Auth::user();

        if ($request->json('data.id') != $dataDb->uuid) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '422',
                'code' => '422',
                'title' => 'invalid request',
                'detail' => 'id resource is invalid.',
                'source' => ['pointer' => 'data/id'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        }

        $validator = Validator::make($request->json('data.attributes'), [
            'fullname' => [
                'filled',
                'string',
                'between:2,100'
            ],
            'username' => [
                'filled',
                'string',
                Rule::unique(\App\Models\User::class, 'username')->ignore($dataDb),
                'max:50'
            ],
            'email' => [
                'filled',
                'string',
                'email',
                Rule::unique(\App\Models\User::class, 'email')->ignore($dataDb),
                'max:50'
            ],
            'phone' => [
                'filled',
                'string',
                Rule::unique(\App\Models\User::class, 'phone')->ignore($dataDb),
                'max:50'
            ],
            'dateOfBirth' => [
                'filled',
                'date',
                'date_format:Y-m-d',
            ],
            'address' => [
                'filled',
                'string',
            ],
            'image' => [
                'filled',
            ],
            'passwordOld' => [
                'filled',
                'string',
                'min:5',
                'required_with:password'
            ],
            'password' => [
                'filled',
                'string',
                'min:5',
                'different:passwordOld',
                'required_with:passwordOld,passwordConfirm'
            ],
            'passwordConfirm' => [
                'filled',
                'string',
                'same:password',
                'required_with:password'
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->toArray();
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        }

        if ($request->has('data.attributes.password')) {
            if (! Hash::check($request->json('data.attributes.passwordOld'), $dataDb->password)) {
                $errorMsg['errors'][] = [
                    'id' => (int) mt_rand(1000, 9999),
                    'status' => '422',
                    'code' => '422',
                    'title' => 'invalid request',
                    'detail' => 'Old Password is invalid.',
                    'source' => ['pointer' => 'data/id'],
                ];
                throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
            }
        }
    }

    public function photoUploadValidation(object $request, string $type): void
    {
        $validator = Validator::make($request->all(), [
            'image' => [
                'required',
                'filled',
                'image',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->toArray();
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        }
    }
}
