<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Data;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Trades extends Resource
{
    /**
     * Get executed trades history where outcome tokens were bought or sold.
     *
     * @param string               $user    User wallet address
     * @param array<string, mixed> $filters Additional filters (marketId, limit, offset, etc.)
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function get(string $user, array $filters = []): array
    {
        $response = $this->httpClient->get('/trades', [
            'user' => $user,
            ...$filters,
        ]);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Count unique markets a user has traded.
     *
     * @param string $user User wallet address
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function traded(string $user): array
    {
        $response = $this->httpClient->get('/traded', [
            'user' => $user,
        ]);

        return $response->json();
    }
}
