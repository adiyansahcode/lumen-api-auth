<?php

declare(strict_types=1);

namespace App\Api\V1\Controllers;

use App\Api\V1\Transformers\AuthTransformer as DataTransformer;
use App\Api\V1\Validations\AuthValidation as DataValidation;
use App\Models\User as DataDb;
use App\Traits\Base64ToImageTrait;
use App\Traits\TransformerTrait;
use Carbon\Carbon;
use Dingo\Api\Http\Response as DingoResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends ApiController
{
    use TransformerTrait;
    use Base64ToImageTrait;

    /**
     * request variable.
     *
     * @var Request
     */
    private $request;

    /**
     * The version of resources.
     *
     * @var string
     */
    private $version;

    /**
     * The name of resources.
     *
     * @var string
     */
    private $type;

    /**
     * The url of resources.
     *
     * @var string
     */
    private $url;

    /**
     * The name of the model.
     *
     * @var string
     */
    private $model;

    /**
     * The name of the fractal tranform.
     *
     * @var string
     */
    private $transformer;

    /**
     * __construct function.
     *
     * @param DataValidation $validation
     */
    public function __construct(DataValidation $validation)
    {
        $this->middleware(
            'auth:api',
            [
                'except' => [
                    'tokenCreate',
                    'register',
                    'tokenRegister',
                ],
            ]
        );
        $this->request = request();
        $this->version = 'v1';
        $this->type = 'auth';
        $this->url = 'auth';
        $this->model = DataDb::class;
        $this->validation = $validation;
        $this->transformer = DataTransformer::class;
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Dingo\Api\Http\Response
     */
    protected function respondWithToken(string $token): DingoResponse
    {
        $responseData = [
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => Auth::factory()->getTTL() * 60,
        ];

        return $this->response
            ->array($responseData)
            ->withHeader('Allow', 'GET,HEAD,OPTIONS,POST,PUT,DELETE');
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Dingo\Api\Exception\ValidationHttpException
     */
    public function tokenRegister()
    {
        $this->validation->tokenRegister($this->request, $this->type);

        $data = new $this->model();
        $credentials = $data->firstWhere('uuid', $this->request->uuid);
        $token = Auth::login($credentials);

        if (empty($token)) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '401',
                'code' => '401',
                'title' => 'invalid login',
                'detail' => 'user or password is invalid',
                'source' => [
                    'parameter' => 'username',
                ],
            ];

            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 401);
        }

        $auth = Auth::user();
        $auth->last_login_at = Carbon::now()->toDateTimeString();
        $auth->last_login_ip = $this->request->getClientIp();
        $auth->save();

        return $this->respondWithToken($token);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Dingo\Api\Exception\ValidationHttpException
     */
    public function tokenCreate(): DingoResponse
    {
        $this->validation->tokenCreate($this->request, $this->type);

        $username = $this->request->username;
        if (is_numeric($username)) {
            $credentials = [
                'phone' => $username,
                'password' => $this->request->password,
            ];
        } elseif (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $credentials = [
                'email' => $username,
                'password' => $this->request->password,
            ];
        } else {
            $credentials = [
                'username' => $username,
                'password' => $this->request->password,
            ];
        }
        $token = null;
        if ($this->request->isRemember === 1) {
            $ttl = 10080; // 1 week
            $token = Auth::setTTL($ttl)->attempt($credentials);
        } else {
            $token = Auth::attempt($credentials);
        }

        if (empty($token)) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '401',
                'code' => '401',
                'title' => 'invalid login',
                'detail' => 'user or password is invalid',
                'source' => [
                    'parameter' => 'username',
                ],
            ];

            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 401);
        }

        $auth = Auth::user();
        $auth->last_login_at = Carbon::now()->toDateTimeString();
        $auth->last_login_ip = $this->request->getClientIp();
        $auth->save();

        return $this->respondWithToken($token);
    }

    /**
     * Refresh a token.
     *
     * @return \Dingo\Api\Http\Response
     */
    public function tokenRefresh(): DingoResponse
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Invalidate the token.
     *
     * @return \Dingo\Api\Http\Response
     */
    public function tokenDelete(): DingoResponse
    {
        Auth::logout();

        return $this->response->noContent();
    }

    /**
     * register new user.
     *
     * @return \Dingo\Api\Http\Response
     * @throws \Dingo\Api\Exception\ValidationHttpException
     */
    public function register(): DingoResponse
    {
        $this->validation->registerValidation($this->request, $this->type);

        try {
            $data = new $this->model();
            $data->uuid = $this->request->json('data.id');
            $data->fullname = $this->request->json('data.attributes.fullname');
            $data->username = $this->request->json('data.attributes.username');
            $data->email = $this->request->json('data.attributes.email');
            $data->phone = $this->request->json('data.attributes.phone');
            $data->password = app('hash')->make($this->request->json('data.attributes.password'));
            $data->save();

            $query = [
                'uuid' => $data->uuid,
                'time' => Carbon::now()->addMinutes(10)->timestamp,
            ];
            $linkLocation = $this->getLink($this->url . '/token') . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);

            return $this->response->item(
                $data,
                new $this->transformer(),
                [
                    'key' => $this->type,
                ]
            )
                ->setStatusCode(201)
                ->withHeader('Location', $linkLocation)
                ->withHeader('Allow', 'GET,HEAD,OPTIONS,POST,PUT,PATCH');
        } catch (\Exception $e) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '500',
                'code' => '101',
                'title' => 'internal server error',
                'detail' => 'registration failed, something was wrong',
            ];

            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 500);
        }
    }

    /**
     * Get user details.
     *
     * @return \Dingo\Api\Http\Response
     */
    public function profile(): DingoResponse
    {
        $data = Auth::user();

        return $this->response->item(
            $data,
            new $this->transformer(),
            [
                'key' => $this->type,
            ]
        )
            ->setStatusCode(200)
            ->withHeader('Allow', 'GET,HEAD,OPTIONS,POST,PUT,PATCH');
    }

    public function profileUpdate(): object
    {
        try {
            DB::beginTransaction();

            $this->validation->profileUpdateValidation($this->request, $this->type);

            $data = Auth::user();

            if ($this->request->has('data.attributes.fullname')) {
                $data->fullname = $this->request->json('data.attributes.fullname');
            }

            if ($this->request->has('data.attributes.username')) {
                $data->username = $this->request->json('data.attributes.username');
            }

            if ($this->request->has('data.attributes.email')) {
                $data->email = $this->request->json('data.attributes.email');
            }

            if ($this->request->has('data.attributes.phone')) {
                $data->phone = $this->request->json('data.attributes.phone');
            }

            if ($this->request->has('data.attributes.dateOfBirth')) {
                $data->date_of_birth = $this->request->json('data.attributes.dateOfBirth');
            }

            if ($this->request->has('data.attributes.address')) {
                $data->address = $this->request->json('data.attributes.address');
            }

            if ($this->request->has('data.attributes.image')) {
                $publicPath = env('APP_PATH_PUBLIC') . 'images/';
                if ($data->image) {
                    $oldFile = $publicPath . $data->image;
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $base64Image = $this->request->json('data.attributes.image');
                $fileName = md5($this->request->json('data.id') . time());
                $image = $this->base64ToImage($base64Image, $fileName, 'data/attributes/image');
                $data->image = $image->name;
                $data->image_url = $image->url;
            }

            if ($this->request->has('data.attributes.password')) {
                $data->password = app('hash')->make($this->request->json('data.attributes.password'));
            }

            $data->save();

            DB::commit();

            return $this->response->item(
                $data,
                new $this->transformer(),
                [
                    'key' => $this->type,
                ]
            )
                ->setStatusCode(200)
                ->withHeader('Allow', 'GET,HEAD,OPTIONS,POST,PUT,PATCH');
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function photoUpload(): object
    {
        try {
            DB::beginTransaction();

            $this->validation->photoUploadValidation($this->request, $this->type);

            $data = Auth::user();

            $file = $this->request->file('image');
            $fileOriName = $file->getClientOriginalName();
            $fileExt = $file->getClientOriginalExtension();
            $fileMimeType = $file->getClientMimeType();
            $fileName = md5($data->uuid . time()) . '.' . $fileExt;

            $publicUrl = env('APP_URL_PUBLIC') . 'images/';
            $publicPath = env('APP_PATH_PUBLIC') . 'images/';
            $fileLocationUrl = $publicUrl . $fileName;
            $fileLocationPath = $publicPath . $fileName;

            $oldFile = $publicPath . $data->image;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }

            if ($file->move($publicPath, $fileName)) {
                $data = Auth::user();
                $data->image = $fileName;
                $data->image_url = $fileLocationUrl;
                $data->save();

                DB::commit();

                return $this->response->item(
                    $data,
                    new $this->transformer(),
                    [
                        'key' => $this->type,
                    ]
                )->setStatusCode(200);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
