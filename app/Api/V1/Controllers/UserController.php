<?php

declare(strict_types=1);

namespace App\Api\V1\Controllers;

use App\Api\V1\Transformers\UserTransformer as DataTransformer;
use App\Api\V1\Validations\UserValidation as DataValidation;
use App\Models\User as DataDb;
use App\Traits\Base64ToImageTrait;
use App\Traits\FetchDataTrait;
use App\Traits\TransformerTrait;
use Dingo\Api\Http\Response as DingoResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends ApiController
{
    use FetchDataTrait;
    use Base64ToImageTrait;
    use TransformerTrait;

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
     */
    public function __construct()
    {
        $this->request = request();
        $this->version = 'v1';
        $this->type = 'user';
        $this->url = 'user';
        $this->model = new DataDb();
        $this->transformer = new DataTransformer();
    }

    public function index(): DingoResponse
    {
        $data = new $this->model();
        $data = $this->hasInclude($data);
        $data = $this->hasSort($data);
        $data = $this->hasFilter($data);
        $data = $this->hasPaginate($data);

        return $this->response->paginator(
            $data,
            new $this->transformer(),
            [
                'key' => $this->type,
            ]
        )->setStatusCode(200)
            ->withHeader('Allow', 'GET,HEAD,OPTIONS,POST,PUT,PATCH,DELETE');
    }

    public function show(string $uuid): DingoResponse
    {
        $requestData = ['uuid' => $uuid];
        $validation = new DataValidation();
        $validation->uuidValidation($requestData, $this->type);

        $data = new DataDb();
        $data = $data->firstWhere('uuid', $uuid);

        if (empty($data)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $this->response->item(
            $data,
            new $this->transformer(),
            [
                'key' => $this->type,
            ]
        )->setStatusCode(200)
            ->withHeader('Allow', 'GET,HEAD,OPTIONS,POST,PUT,PATCH,DELETE');
    }

    public function create(): DingoResponse
    {
        try {
            DB::beginTransaction();

            $validation = new DataValidation();
            $validation->createValidation($this->request, $this->type);

            $data = new $this->model();
            $data->uuid = $this->request->json('data.id');
            $data->fullname = $this->request->json('data.attributes.fullname');
            $data->username = $this->request->json('data.attributes.username');
            $data->email = $this->request->json('data.attributes.email');
            $data->phone = $this->request->json('data.attributes.phone');
            $data->date_of_birth = $this->request->json('data.attributes.dateOfBirth');
            $data->password = app('hash')->make($this->request->json('data.attributes.password'));
            $data->address = $this->request->json('data.attributes.address');
            $data->save();

            if ($this->request->has('data.attributes.image')) {
                $base64Image = $this->request->json('data.attributes.image');
                $fileName = md5($this->request->json('data.id') . time());
                $image = $this->base64ToImage($base64Image, $fileName, 'data/attributes/image');
                $data->image = $image->name;
                $data->image_url = $image->url;
            }

            $data->save();

            DB::commit();

            $linkLocation = $this->getLinkSelf($this->url, $data->uuid);
            return $this->response->item(
                $data,
                new $this->transformer(),
                [
                    'key' => $this->type,
                ]
            )->setStatusCode(201)
            ->withHeader('Location', $linkLocation)
            ->withHeader('Allow', 'GET,HEAD,OPTIONS,POST,PUT,PATCH,DELETE');
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function update(string $uuid): DingoResponse
    {
        try {
            DB::beginTransaction();

            $requestData = ['uuid' => $uuid];
            $validation = new DataValidation();
            $validation->uuidValidation($requestData, $this->type);
            $validation->updateValidation($this->request, $this->type);

            $data = new $this->model();
            $data = $data->firstWhere('uuid', $uuid);

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
                if ($data->image) {
                    $publicPath = env('APP_PATH_PUBLIC') . 'images/';
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
            )->setStatusCode(200)
                ->withHeader('Allow', 'GET,HEAD,OPTIONS,POST,PUT,PATCH,DELETE');
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function delete(string $uuid): DingoResponse
    {
        $requestData = ['uuid' => $uuid];
        $validation = new DataValidation();
        $validation->uuidValidation($requestData, $this->type);

        $data = new $this->model();
        $data = $data->firstWhere('uuid', $uuid);

        if (empty($data)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $data->delete();

        return $this->response->noContent()
            ->setStatusCode(204)
            ->withHeader('Allow', 'GET,HEAD,OPTIONS,POST,PUT,PATCH,DELETE');
    }
}
