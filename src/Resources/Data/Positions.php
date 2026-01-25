<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Data;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Positions extends Resource
{
    /**
     * Get current (open) outcome token holdings for a user.
     *
     * @param string               $user    User wallet address
     * @param array<string, mixed> $filters Additional filters (market, conditionId, etc.)
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function get(string $user, array $filters = []): array
    {
        $response = $this->httpClient->get('/positions', [
            'user' => $user,
            ...$filters,
        ]);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get historical positions (fully sold or redeemed).
     *
     * @param string               $user    User wallet address
     * @param array<string, mixed> $filters Additional filters
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function closed(string $user, array $filters = []): array
    {
        $response = $this->httpClient->get('/closed-positions', [
            'user' => $user,
            ...$filters,
        ]);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Calculate total position value, optionally filtered by markets.
     *
     * @param string               $user    User wallet address
     * @param array<string, mixed> $filters Additional filters (marketIds, etc.)
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function value(string $user, array $filters = []): array
    {
        $response = $this->httpClient->get('/value', [
            'user' => $user,
            ...$filters,
        ]);

        return $response->json();
    }
}
