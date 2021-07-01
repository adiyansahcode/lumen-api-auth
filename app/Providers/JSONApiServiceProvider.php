<?php

declare(strict_types=1);

namespace App\Providers;

use App\MyClass\MySerializer;
use App\MyClass\MyValidator;
use Dingo\Api\Exception\ValidationHttpException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JSONApiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Dingo\Api\Transformer\Adapter\Fractal', function ($app) {
            $request = request();
            $baseUrl = url();
            $fullUrl = request()->url();
            $apiDomain = config('api.domain');
            $apiPrefix = config('api.prefix');
            $apiVersion = 'v1';
            if (empty($apiDomain)) {
                $segments = explode('/', trim(Str::replaceFirst($baseUrl, '', $fullUrl)));
                if (count($segments) > 2) {
                    $segment1 = $segments[0];
                    $segment2 = $segments[1];
                    $apiVersion = $segments[2];
                }
                $baseUrlApi = $baseUrl . '/' . $apiPrefix . '/' . $apiVersion;
            } else {
                $segments = explode('/', trim(Str::replaceFirst($baseUrl, '', $fullUrl)));
                if (count($segments) > 2) {
                    $segment1 = $segments[0];
                    $apiVersion = $segments[1];
                }
                $baseUrlApi = $apiDomain . '/' . $apiVersion;
            }
            $serializer = new MySerializer($baseUrlApi);
            // $serializer = new \League\Fractal\Serializer\DataArraySerializer();
            // $serializer = new \League\Fractal\Serializer\JsonApiSerializer();
            $fractal = $app->make(\League\Fractal\Manager::class);
            $fractal->setSerializer($serializer);
            if (! empty($request->query('include'))) {
                $fractal->parseIncludes($request->query('include'));
            }
            if (! empty($request->query('exclude'))) {
                $fractal->parseExcludes($request->query('exclude'));
            }
            if (! empty($request->query('fields'))) {
                $fractal->parseFieldsets($request->query('fields'));
            }

            return new \Dingo\Api\Transformer\Adapter\Fractal($fractal);
        });

        // * 401
        app('Dingo\Api\Exception\Handler')->register(function (UnauthorizedHttpException $exception) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '401',
                'code' => '401',
                'title' => 'Unauthorized',
                'detail' => $exception->getMessage(),
            ];

            return response($errorMsg, 401);
        });

        // * 403
        app('Dingo\Api\Exception\Handler')->register(function (AccessDeniedHttpException $exception) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '403',
                'code' => '403',
                'title' => 'Forbidden',
                'detail' => $exception->getMessage(),
            ];

            return response($errorMsg, 403);
        });

        // * 404
        app('Dingo\Api\Exception\Handler')->register(function (NotFoundHttpException $exception) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '404',
                'code' => '404',
                'title' => 'Not Found',
                'detail' => 'request not found',
            ];

            return response($errorMsg, 404);
        });

        // * 405
        app('Dingo\Api\Exception\Handler')->register(function (MethodNotAllowedHttpException $exception) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '405',
                'code' => '405',
                'title' => 'Method Not Allowed',
                'detail' => 'method request not allowed',
            ];

            return response($errorMsg, 405);
        });

        // * 400, 422
        app('Dingo\Api\Exception\Handler')->register(function (ValidationHttpException $exception) {
            if (empty($exception->getCode())) {
                $code = 422;
            } else {
                $code = $exception->getCode();
            }

            return response()
                ->json($exception->getErrors(), $code, ['Content-Type' => 'application/vnd.api+json'])
                ->header('Content-Type', 'application/vnd.api+json');
        });

        // * 500
        app('Dingo\Api\Exception\Handler')->register(function (HttpException $exception) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '500',
                'code' => (string) $exception->getCode(),
                'title' => 'Internal Server Error',
                'detail' => 'something wrong, ' . $exception->getMessage(),
            ];

            return response($errorMsg, 500)->header('Content-Type', 'application/vnd.api+json');
        });

        // * 500
        app('Dingo\Api\Exception\Handler')->register(function (QueryException $exception) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '500',
                'code' => (string) $exception->getCode(),
                'title' => 'Internal Server Error',
                // 'detail' => 'something wrong, query invalid',
                'detail' => 'something wrong, ' . $exception->getMessage(),
            ];

            return response($errorMsg, 500)->header('Content-Type', 'application/vnd.api+json');
        });
    }

    public function boot()
    {
        Validator::resolver(function ($translator, $data, $rules, $messages) {
            return new MyValidator($translator, $data, $rules, $messages);
        });
    }
}
