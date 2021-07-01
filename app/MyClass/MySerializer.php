<?php

declare(strict_types=1);

namespace App\MyClass;

use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Pagination\CursorInterface;
use Illuminate\Support\Str;

class MySerializer extends JsonApiSerializer
{
    /**
     * Serialize the paginator.
     *
     * @param PaginatorInterface $paginator
     *
     * @return array
     */
    public function paginator(PaginatorInterface $paginator): array
    {
        $currentPage = (int) $paginator->getCurrentPage();
        $lastPage = (int) $paginator->getLastPage();

        $pagination = [
            'total' => (int)$paginator->getTotal(),
            'count' => (int)$paginator->getCount(),
            'perPage' => (int)$paginator->getPerPage(),
            'currentPage' => $currentPage,
            'totalPages' => $lastPage,
        ];

        $pagination['links'] = [];

        $pagination['links']['self'] = $paginator->getUrl($currentPage);
        $pagination['links']['first'] = $paginator->getUrl(1);

        if ($currentPage > 1) {
            $pagination['links']['prev'] = $paginator->getUrl($currentPage - 1);
        } else {
            $pagination['links']['prev'] = null;
        }

        if ($currentPage < $lastPage) {
            $pagination['links']['next'] = $paginator->getUrl($currentPage + 1);
        } else {
            $pagination['links']['next'] = null;
        }

        $pagination['links']['last'] = $paginator->getUrl($lastPage);

        return ['pagination' => $pagination];
    }

    /**
     * Serialize the cursor.
     *
     * @param CursorInterface $cursor
     *
     * @return array
     */
    public function cursor(CursorInterface $cursor): array
    {
        $cursor = [
            'current' => $cursor->getCurrent(),
            'prev' => $cursor->getPrev(),
            'next' => $cursor->getNext(),
            'count' => (int) $cursor->getCount(),
        ];

        return ['cursor' => $cursor];
    }

    /**
     * {@inheritdoc}
     */
    public function injectAvailableIncludeData($data, $availableIncludes)
    {
        if (!$this->shouldIncludeLinks()) {
            return $data;
        }

        if ($this->isCollection($data)) {
            $data['data'] = array_map(function ($resource) use ($availableIncludes) {
                foreach ($availableIncludes as $relationshipKey) {
                    $resource = $this->addRelationshipLinks($resource, $relationshipKey);
                }
                return $resource;
            }, $data['data']);
        } else {
            foreach ($availableIncludes as $relationshipKey) {
                $data['data'] = $this->addRelationshipLinks($data['data'], $relationshipKey);
            }
        }

        return $data;
    }

    /**
     * Adds links for all available includes to a single resource.
     *
     * @param array $resource         The resource to add relationship links to
     * @param string $relationshipKey The resource key of the relationship
     */
    private function addRelationshipLinks($resource, $relationshipKey)
    {
        if (!isset($resource['relationships']) || !isset($resource['relationships'][$relationshipKey])) {
            $resource['relationships'][$relationshipKey] = [];
        }

        $type = Str::of($resource['type'])->kebab();
        $relationshipType = Str::of($relationshipKey)->kebab();

        $resource['relationships'][$relationshipKey] = array_merge(
            [
                'links' => [
                    'self'   => "{$this->baseUrl}/{$type}/{$resource['id']}/relationships/{$relationshipType}",
                    'related' => "{$this->baseUrl}/{$type}/{$resource['id']}/{$relationshipType}",
                ]
            ],
            $resource['relationships'][$relationshipKey]
        );

        return $resource;
    }
}
