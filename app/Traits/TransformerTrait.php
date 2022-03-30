<?php

declare(strict_types=1);

namespace App\Traits;

trait TransformerTrait
{
    public function getLink(string $url, ?string $id = null): string
    {
        $apiVersion = 'v1';
        $apiDomain = config('api.domain');
        $apiPrefix = config('api.prefix');

        if (!empty($id)) {
            $id = '/' . $id;
        }

        if (empty($apiDomain)) {
            $linkSelf = url() . '/' . $apiPrefix . '/' . $apiVersion . '/' . $url . $id;
        } else {
            $linkSelf = $apiDomain . '/' . $apiVersion . '/' . $url . $id;
        }

        return $linkSelf;
    }

    public function getLinkSelf(string $url, ?string $id = null): string
    {
        $apiVersion = 'v1';
        $apiDomain = config('api.domain');
        $apiPrefix = config('api.prefix');

        if (!empty($id)) {
            $id = '/' . $id;
        }

        if (empty($apiDomain)) {
            $linkSelf = url() . '/' . $apiPrefix . '/' . $apiVersion . '/' . $url . $id;
        } else {
            $linkSelf = $apiDomain . '/' . $apiVersion . '/' . $url . $id;
        }

        return $linkSelf;
    }
}
