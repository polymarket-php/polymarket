<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Data;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Activity extends Resource
{
    /**
     * Get on-chain operations including trades, splits, merges, redemptions, rewards, and conversions.
     *
     * @param string               $user    User wallet address
     * @param array<string, mixed> $filters Additional filters (type, limit, offset, etc.)
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function get(string $user, array $filters = []): array
    {
        $response = $this->httpClient->get('/activity', [
            'user' => $user,
            ...$filters,
        ]);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }
}
