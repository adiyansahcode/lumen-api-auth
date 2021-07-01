<?php

declare(strict_types=1);

namespace App\Api\V1\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User as DataDb;
use App\Traits\TransformerTrait;

class AuthTransformer extends TransformerAbstract
{
    use TransformerTrait;

    public $type = 'auth';

    public $url = 'auth';

    protected $availableIncludes = [];

    /**
     * transform function
     *
     * @param DataDb $data
     * @return array
     */
    public function transform(DataDb $data): array
    {
        $linkSelf = $this->getLink($this->url);

        return [
            'id' => (string) $data->uuid,
            'createdAt' => (string) $data->created_at,
            'updatedAt' => (string) $data->updated_at,
            'fullname' => (string) $data->fullname,
            'username' => (string) $data->username,
            'email' => (string) $data->email,
            'phone' => (string) $data->phone,
            'dateOfBirth' => (string) $data->date_of_birth,
            'address' => (string) $data->address,
            'image' => (string) $data->image,
            'imageUrl' => (string) $data->image_url,
            'lastLoginAt' => (string) $data->last_login_at,
            'lastLoginIp' => (string) $data->last_login_ip,
            'links' => [
                'self' => $linkSelf,
            ],
        ];
    }
}
