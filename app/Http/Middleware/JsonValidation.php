<?php

namespace App\Http\Middleware;

use Closure;

class JsonValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('Content-Type') != 'application/json') {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'Bad Request',
                'detail' => 'Content-Type request must application/json',
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        // Attempt to decode payload
        json_decode($request->getContent());
        if (json_last_error() != JSON_ERROR_NONE) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '400',
                'code' => '400',
                'title' => 'Bad Request',
                'detail' => 'The request is not a valid JSON.',
            ];
            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 400);
        }

        return $next($request);
    }
}
