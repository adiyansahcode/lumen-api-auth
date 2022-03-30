<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

trait ValidationTrait
{
    public function validateInclude(): void
    {
        $request = request();

        $validator = Validator::make($request->all(), [
            'include' => [
                'string',
                'filled',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->toArray();
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        if (! empty($request->query('include'))) {
            $transformerData = new $this->transformer();
            $column = $transformerData->getAvailableIncludes();

            $data = request()->query('include');
            $dataArray = Str::of($data)->explode(',');
            foreach ($dataArray as $dataValue) {
                if (! in_array($dataValue, $column)) {
                    $errorMsg['errors'][] = [
                        'id' => (int) mt_rand(1000, 9999),
                        'status' => '400',
                        'code' => '422',
                        'title' => 'invalid relationships',
                        'detail' => 'relationships not exist',
                        'source' => ['parameter' => 'include'],
                    ];
                    throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
                }
            }
        }
    }

    public function validateSort(): void
    {
        $request = request();

        $validator = Validator::make($request->all(), [
            'sort' => [
                'string',
                'filled',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->toArray();
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        if (! empty($request->query('sort'))) {
            $model = new $this->model();
            $tableName = $model->getTable();

            $sortColumn = $model->sortable;
            if (empty($sortColumn)) {
                $errorMsg['errors'][] = [
                    'id' => (int) mt_rand(1000, 9999),
                    'status' => '400',
                    'code' => '422',
                    'title' => 'invalid sorting',
                    'detail' => 'API does not support sorting parameter',
                    'source' => ['parameter' => 'sort'],
                ];
                throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
            }

            $column = [];
            foreach ($sortColumn as $columnData) {
                $column[] = Str::camel($columnData);
                $column[] = '-' . Str::camel($columnData);
                $column[] = $tableName . '.' . Str::camel($columnData);
                $column[] = $tableName . '.' . '-' . Str::camel($columnData);
            }

            $data = request()->query('sort');
            $dataArray = Str::of($data)->explode(',');
            foreach ($dataArray as $dataValue) {
                if (! in_array($dataValue, $column)) {
                    $errorMsg['errors'][] = [
                        'id' => (int) mt_rand(1000, 9999),
                        'status' => '400',
                        'code' => '422',
                        'title' => 'invalid sorting',
                        'detail' => 'sorting column is invalid',
                        'source' => ['parameter' => 'sort'],
                    ];
                    throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
                }
            }
        }
    }

    public function validateFilter(): void
    {
        $request = request();

        // filter must array
        $validator = Validator::make($request->all(), [
            'filter' => [
                'array',
                'filled',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->toArray();
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        // get model and table name
        $model = new $this->model();
        $tableName = $model->getTable();

        // check if model set filtering column
        $filterColumn = $model->filterable;

        if (empty($filterColumn)) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '105',
                'title' => 'invalid filtering',
                'detail' => 'API does not support filtering parameter',
                'source' => ['parameter' => 'filter'],
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        // list all filtering column
        $column = [];
        foreach ($filterColumn as $columnData) {
            $column[] = Str::camel($columnData);
            $column[] = Str::camel($tableName) . '.' . Str::camel($columnData);
        }

        $transformerData = new $this->transformer();
        $dataArray = $transformerData->getAvailableIncludes();
        foreach ($dataArray as $dataValue) {
            $column[] = $dataValue;
            $column[] = $dataValue . '.name';
            $column[] = $dataValue . '.id';
        }

        // Logical Operators
        $filterRule = [
            'eq', // operator (Equals)
            'not', // operator (Not Equals)
            'gt', // operator (Greater Than)
            'gteq', // operator (Greater Than or Equal)
            'lt', // operator (Less Than)
            'lteq', // operator (Less Than or Equal)
            'in', // operator (In)
            'notin', // operator (Not In)
            'like', // operator string (filters if a string like another substring)
        ];

        if (! empty($request->query('filter'))) {
            $filter = request()->query('filter');
            foreach ($filter as $filterKey => $filterValue) {
                if (! in_array($filterKey, $column)) {
                    $errorMsg['errors'][] = [
                        'id' => (int) mt_rand(1000, 9999),
                        'status' => '400',
                        'code' => '105',
                        'title' => 'invalid filtering',
                        'detail' => 'filter column is invalid',
                        'source' => ['parameter' => 'filter'],
                    ];
                    throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
                }

                if (! is_array($filterValue)) {
                    $filterValue = [
                        'eq' => $filterValue
                    ];
                }

                foreach ($filterValue as $filterKey2 => $filterValue2) {
                    $operator = $filterKey2;
                    if (! in_array($operator, $filterRule)) {
                        $errorMsg['errors'][] = [
                            'id' => (int) mt_rand(1000, 9999),
                            'status' => '400',
                            'code' => '105',
                            'title' => 'invalid filtering',
                            'detail' => 'filtering operators is invalid',
                            'source' => ['parameter' => 'filter'],
                        ];
                        throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
                    }

                    if ($filterValue2 === '') {
                        $errorMsg['errors'][] = [
                            'id' => (int) mt_rand(1000, 9999),
                            'status' => '400',
                            'code' => '105',
                            'title' => 'invalid filtering',
                            'detail' => 'the filter field must have a value.',
                            'source' => [
                                'parameter' => "filter/$filterKey/$operator"
                            ],
                        ];
                        throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
                    }
                }
            }
        }
    }

    public function validatePaginate(): void
    {
        $request = request();

        $validator = Validator::make($request->all(), [
            'page' => [
                'array',
            ],
            'page.limit' => [
                'integer',
                'filled',
                'gt:0',
                'required_with:page.offset',
            ],
            'page.offset' => [
                'integer',
                'filled',
                'gt:0',
            ],
            'page.size' => [
                'integer',
                'filled',
                'gt:0',
                'required_with:page.number',
                'required_with:page.after',
                'required_with:page.before',
            ],
            'page.number' => [
                'integer',
                'filled',
                'gt:0',
            ],
            'page.after' => [
                'string',
                'filled',
                'uuid',
                'exists:book,uuid',
            ],
            'page.before' => [
                'string',
                'filled',
                'uuid',
                'exists:book,uuid',
            ],
        ]);

        if ($validator->fails()) {
            $errorMsg = $validator->errors()->toArray();
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }
    }
}
