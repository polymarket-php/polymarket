<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket\Resources\Gamma;

use PolymarketPhp\Polymarket\Exceptions\PolymarketException;
use PolymarketPhp\Polymarket\Resources\Resource;

class Series extends Resource
{
    /**
     * List series.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function list(array $filters = []): array
    {
        $response = $this->httpClient->get('/series', [
            ...$filters,
        ]);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get series by ID.
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $seriesId): array
    {
        $response = $this->httpClient->get("/series/$seriesId");

        return $response->json();
    }
}
