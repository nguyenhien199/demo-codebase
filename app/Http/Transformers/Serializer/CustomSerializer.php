<?php

namespace App\Http\Transformers\Serializer;

use League\Fractal\Serializer\ArraySerializer;

/**
 * Class CustomSerializer
 * @package App\Http\Transformers\Serializer
 */
class CustomSerializer extends ArraySerializer
{
    /**
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array|array[]
     */
    public function collection($resourceKey, array $data): array
    {
        return $data;
    }

    /**
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function item($resourceKey, array $data): array
    {
        return $data;
    }

    /**
     * @return array|array[]
     */
    public function null(): ?array
    {
        return ['data' => []];
    }
}
