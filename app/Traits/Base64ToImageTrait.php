<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

trait Base64ToImageTrait
{
    public function base64Validation(string $base64, string $param): void
    {
        // By default PHP will ignore “bad” characters, so we need to enable the “$strict” mode
        $base64Check = base64_decode($base64, true);

        // If $input cannot be decoded the $str will be a Boolean “FALSE”
        if ($base64Check === false) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '422',
                'code' => '422',
                'title' => 'invalid request',
                'detail' => 'base64 is invalid.',
                'source' => [
                    'pointer' => $param
                ],
            ];

            throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
        } else {
            // Even if $str is not FALSE, this does not mean that the input is valid
            // This is why now we should encode the decoded string and check it against input
            $base64New = base64_encode($base64Check);

            // Finally, check if input string and real Base64 are identical
            if ($base64 !== $base64New) {
                $errorMsg['errors'][] = [
                    'id' => (int) mt_rand(1000, 9999),
                    'status' => '422',
                    'code' => '422',
                    'title' => 'invalid request',
                    'detail' => 'base64 is invalid.',
                    'source' => [
                        'pointer' => $param
                    ],
                ];

                throw new \Dingo\Api\Exception\ValidationHttpException($errorMsg, null, [], 422);
            }
        }
    }

    public function base64ToImage(string $base64, string $fileName, string $param): object
    {
        $publicUrl = env('APP_URL_PUBLIC') . 'images/';
        $publicPath = env('APP_PATH_PUBLIC') . 'images/';
        $base64String = $base64;
        $ext = '.png';

        // check base64
        if (Str::contains($base64, 'base64,')) {
            // split the string
            // $data[ 0 ] == "data:image/png;base64"
            // $data[ 1 ] == <actual base64 string>
            $data = explode('base64,', $base64);
            $base64String = $data[1];

            if ($data[0] === 'data:image/png;') {
                $ext = '.png';
            } elseif ($data[0] === 'data:image/jpeg;') {
                $ext = '.jpeg';
            } elseif ($data[0] === 'data:image/jpg;') {
                $ext = '.jpg';
            }
        }
        $fileName = $fileName . $ext;
        $fileLocationUrl = $publicUrl . $fileName;
        $fileLocationPath = $publicPath . $fileName;

        $this->base64Validation($base64String, $param);

        // open the output file for writing
        $ifp = fopen($fileLocationPath, 'wb');

        // we could add validation here with ensuring count( $data ) > 1
        fwrite($ifp, base64_decode($base64String));

        // clean up the file resource
        fclose($ifp);

        $object = new \stdClass();
        $object->name = $fileName;
        $object->url = $fileLocationUrl;
        $object->path = $fileLocationPath;

        return $object;
    }
}
