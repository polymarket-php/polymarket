<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Data;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Leaderboards extends Resource
{
    /**
     * Get trader rankings by category and time period.
     *
     * @param array<string, mixed> $filters Filters (category, period, limit, offset, etc.)
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function get(array $filters = []): array
    {
        $response = $this->httpClient->get('/leaderboard', $filters);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get aggregated third-party application rankings.
     *
     * @param array<string, mixed> $filters Filters (period, limit, offset, etc.)
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function builder(array $filters = []): array
    {
        $response = $this->httpClient->get('/builder-leaderboard', $filters);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get daily time-series builder volume data.
     *
     * @param array<string, mixed> $filters Filters (startDate, endDate, builderId, etc.)
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function builderVolume(array $filters = []): array
    {
        $response = $this->httpClient->get('/builder-volume', $filters);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }
}
