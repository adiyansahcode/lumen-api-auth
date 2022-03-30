<?php

declare(strict_types=1);

namespace App\Api\V1\Transformers;

use App\Models\User as DataDb;
use App\Traits\TransformerTrait;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    use TransformerTrait;

    public $type = 'user';

    public $url = 'user';

    protected $availableIncludes = [];

    /**
     * transform function
     *
     * @param InboxType $data
     * @return array
     */
    public function transform(DataDb $data): array
    {
        $linkSelf = $this->getLinkSelf($this->url, $data->uuid);

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
