<?php

declare(strict_types=1);

namespace App\MyClass;

use Dingo\Api\Http\Response\Format\Json;

class DingoJsonApiFormat extends Json
{
    /**
     * Get the response content type.
     *
     * @return string
     */
    public function getContentType()
    {
        return 'application/vnd.api+json';
    }
}
