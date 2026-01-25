<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Data;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Analytics extends Resource
{
    /**
     * Get top token holders per market.
     *
     * @param string               $conditionId Market condition ID
     * @param array<string, mixed> $filters     Additional filters (limit, offset, etc.)
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function holders(string $conditionId, array $filters = []): array
    {
        $response = $this->httpClient->get('/holders', [
            'condition_id' => $conditionId,
            ...$filters,
        ]);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get total value of outstanding positions in a market.
     *
     * @param string               $conditionId Market condition ID
     * @param array<string, mixed> $filters     Additional filters
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function openInterest(string $conditionId, array $filters = []): array
    {
        $response = $this->httpClient->get('/open-interest', [
            'condition_id' => $conditionId,
            ...$filters,
        ]);

        return $response->json();
    }

    /**
     * Track trading volume with per-market breakdown.
     *
     * @param array<string, mixed> $filters Filters (conditionId, startDate, endDate, etc.)
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function liveVolume(array $filters = []): array
    {
        $response = $this->httpClient->get('/live-volume', $filters);

        return $response->json();
    }
}
