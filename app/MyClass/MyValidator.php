<?php

declare(strict_types=1);

namespace App\MyClass;

use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;

class MyValidator extends Validator
{
    /**
     * Add an error message to the validator's collection of messages.
     *
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array   $parameters
     * @return void
     */
    public function addFailure($attribute, $rule, $parameters = [])
    {
        if (!$this->messages) {
            $this->passes();
        }

        $attribute = str_replace(
            [$this->dotPlaceholder, '__asterisk__'],
            ['.', '*'],
            $attribute
        );

        if (in_array($rule, $this->excludeRules)) {
            return $this->excludeAttribute($attribute);
        }

        $message = $this->makeReplacements(
            $this->getMessage($attribute, $rule),
            $attribute,
            $rule,
            $parameters
        );

        $ruleName = strtolower(Str::snake($rule));
        $code = $this->ruleToCode($ruleName);
        $title = str_replace('_', ' ', $ruleName);

        $request = request();
        $method = $request->method();
        $path = $request->path();
        $url = $request->url();
        $fullUrl = $request->fullUrl();
        $route = $request->route();
        if ($method === 'GET') {
            $source = [
                'parameter' => str_replace('.', '/', $attribute)
            ];
        } elseif ($method === 'DELETE') {
            $source = [
                'parameter' => $attribute
            ];
        } else {
            if ($path === 'api/v1/auth') {
                $source = [
                    'pointer' => $attribute
                ];
            } else {
                if ($attribute === 'id') {
                    $source = [
                        'pointer' => '/data/' . $attribute
                    ];
                } else {
                    $source = [
                        'pointer' => '/data/attributes/' . $attribute
                    ];
                }
            }
        }

        $customMessage = new MessageBag();
        $customMessage->merge(
            [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '422',
                'code' => $code,
                'title' => $title,
                'detail' => $message,
                'source' => $source
            ]
        );
        $this->messages->add('errors', $customMessage);

        $this->failedRules[$attribute][$rule] = $parameters;
    }

    /**
     * Validate an attribute using a custom rule object.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Illuminate\Contracts\Validation\Rule  $rule
     * @return void
     */
    public function validateUsingCustomRule($attribute, $value, $rule)
    {
        $attribute = $this->replacePlaceholderInString($attribute);

        $value = is_array($value) ? $this->replacePlaceholders($value) : $value;

        if (! $rule->passes($attribute, $value)) {
            $this->failedRules[$attribute][get_class($rule)] = [];

            $messages = $rule->message();

            $messages = $messages ? (array) $messages : [get_class($rule)];

            foreach ($messages as $message) {
                $message = $this->makeReplacements($message, $attribute, get_class($rule), []);
                $ruleName = strtolower(Str::snake(get_class($rule)));
                $code = $this->ruleToCode($ruleName);
                $title = str_replace('_', ' ', $ruleName);

                $request = request();
                $method = $request->method();
                if ($method === 'GET') {
                    $source = [
                        'parameter' => $attribute
                    ];
                } elseif ($method === 'DELETE') {
                    $source = [
                        'parameter' => $attribute
                    ];
                } else {
                    $source = [
                        'pointer' => '/data/attributes/' . $attribute
                    ];
                }

                $customMessage = new MessageBag();
                $customMessage->merge(
                    [
                        'id' => (int) mt_rand(1000, 9999),
                        'status' => '422',
                        'code' => $code,
                        'title' => $title,
                        'detail' => $message,
                        'source' => $source
                    ]
                );
                $this->messages->add('errors', $customMessage);
            }
        }
    }

    private function ruleToCode(string $rule): string
    {
        $map = [
            'accepted' => '001',
            'active_url' => '002',
            'after' => '003',
            'after_or_equal' => '004',
            'alpha' => '005',
            'alpha_dash' => '006',
            'alpha_num' => '007',
            'array' => '008',
            'before' => '009',
            'before_or_equal' => '010',
            'between' => '011',
            'boolean' => '012',
            'confirmed' => '013',
            'date' => '014',
            'date_equals' => '015',
            'date_format' => '016',
            'different' => '017',
            'digits' => '018',
            'digits_between' => '019',
            'dimensions' => '020',
            'distinct' => '021',
            'email' => '022',
            'ends_with' => '023',
            'exists' => '024',
            'file' => '025',
            'filled' => '026',
            'gt' => '027',
            'gte' => '028',
            'image' => '029',
            'in' => '030',
            'in_array' => '031',
            'integer' => '032',
            'ip' => '033',
            'ipv4' => '034',
            'ipv6' => '035',
            'json' => '036',
            'lt' => '037',
            'lte' => '038',
            'max' => '039',
            'mimes' => '040',
            'mimetypes' => '041',
            'min' => '042',
            'multiple_of' => '043',
            'not_in' => '044',
            'not_regex' => '045',
            'numeric' => '046',
            'password' => '047',
            'present' => '048',
            'regex' => '049',
            'required' => '050',
            'required_if' => '051',
            'required_unless' => '052',
            'required_with' => '053',
            'required_with_all' => '054',
            'required_without' => '055',
            'required_without_all' => '056',
            'same' => '057',
            'size' => '058',
            'starts_with' => '059',
            'string' => '060',
            'timezone' => '061',
            'unique' => '062',
            'uploaded' => '063',
            'url' => '064',
            'uuid' => '065',
            'login_invalid' => '100',
            'register_invalid' => '101',
            'save_invalid' => '102',
            'update_invalid' => '103',
            'delete_invalid' => '104',
            'filtering_invalid' => '105',
            'upload_invalid' => '106',
            'register_allo_invalid' => '200',
            'login_allo_invalid' => '201',
            'scan_invalid' => '202',
        ];

        if (isset($map[$rule])) {
            return $map[$rule];
        } else {
            return '400';
        }
    }
}
