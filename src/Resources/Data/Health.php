<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Data;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Health extends Resource
{
    /**
     * Check Data API operational status.
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function check(): array
    {
        $response = $this->httpClient->get('/health');

        return $response->json();
    }
}
