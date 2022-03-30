<?php

declare(strict_types=1);

namespace App\Api\V1\Validations;

use App\Models\User as DataDb;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserValidation
{
    public function uuidValidation(array $request, string $type): void
    {
        $validator = Validator::make($request, [
            'uuid' => [
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
                'detail' => 'uuid is invalid format.',
                'source' => ['parameter' => 'uuid'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        $validator = Validator::make($request, [
            'uuid' => [
                'exists:' . DataDb::class . ',uuid',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'invalid request',
                'detail' => 'uuid resource is not exists.',
                'source' => ['parameter' => 'uuid'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }
    }

    public function createValidation(object $request, string $type): void
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
                Rule::unique(DataDb::class, 'uuid')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
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
                Rule::unique(DataDb::class, 'username')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
                function ($attribute, $value, $fail) {
                    // check unique case sensitive
                    $data = DataDb::whereNull('deleted_at')->whereRaw('LOWER(username) = ?', [Str::lower($value)])->first();
                    if ($data) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
                'max:50'
            ],
            'email' => [
                'filled',
                'string',
                'email:rfc,dns',
                Rule::unique(DataDb::class, 'email')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
                function ($attribute, $value, $fail) {
                    // check unique case sensitive
                    $data = DataDb::whereNull('deleted_at')->whereRaw('LOWER(email) = ?', [Str::lower($value)])->first();
                    if ($data) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
                'max:100'
            ],
            'phone' => [
                'required',
                'filled',
                'numeric',
                Rule::unique(DataDb::class, 'phone')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
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
            'image' => [
                'filled',
                'string'
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->toArray();
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        }
    }

    public function updateValidation(object $request, string $type): void
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
                'exists:' . DataDb::class . ',uuid',
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

        $uuid = $request->json('data.id');

        if ($request->json('data.id') != $uuid) {
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
                Rule::unique(DataDb::class, 'username')->ignore($uuid, 'uuid')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
                function ($attribute, $value, $fail) use ($uuid) {
                    // check unique case sensitive
                    $data = DataDb::whereNull('deleted_at')->where('uuid', '<>', $uuid)->whereRaw('LOWER(username) = ?', [Str::lower($value)])->first();
                    if ($data) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
                'max:50'
            ],
            'email' => [
                'filled',
                'string',
                'email:rfc,dns',
                Rule::unique(DataDb::class, 'email')->ignore($uuid, 'uuid')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
                function ($attribute, $value, $fail) use ($uuid) {
                    // check unique case sensitive
                    $data = DataDb::whereNull('deleted_at')->where('uuid', '<>', $uuid)->whereRaw('LOWER(email) = ?', [Str::lower($value)])->first();
                    if ($data) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
                'max:100'
            ],
            'phone' => [
                'filled',
                'numeric',
                Rule::unique(DataDb::class, 'phone')->ignore($uuid, 'uuid')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
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
    }
}
