<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Gamma;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Http\BatchResult;
use Danielgnh\PolymarketPhp\Http\Response;
use Danielgnh\PolymarketPhp\Resources\Resource;
use Danielgnh\PolymarketPhp\Resources\Traits\HasAsyncClient;
use GuzzleHttp\Promise\PromiseInterface;

class Markets extends Resource
{
    use HasAsyncClient;

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function list(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $response = $this->httpClient->get('/markets', [
            'limit' => $limit,
            'offset' => $offset,
            ...$filters,
        ]);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $marketId): array
    {
        $response = $this->httpClient->get("/markets/$marketId");

        return $response->json();
    }

    /**
     * Get market by slug.
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getBySlug(string $slug): array
    {
        $response = $this->httpClient->get("/markets/slug/$slug");

        return $response->json();
    }

    /**
     * Get all tags associated with a specific market.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function tags(string $marketId): array
    {
        $response = $this->httpClient->get("/markets/$marketId/tags");

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function listAsync(array $filters = [], int $limit = 100, int $offset = 0): PromiseInterface
    {
        return $this->getAsyncClient()->getAsync('/markets', [
            'limit' => $limit,
            'offset' => $offset,
            ...$filters,
        ])->then(fn (Response $response): array => $response->json());
    }

    public function getAsync(string $marketId): PromiseInterface
    {
        return $this->getAsyncClient()->getAsync("/markets/$marketId")
            ->then(fn (Response $response): array => $response->json());
    }

    public function getBySlugAsync(string $slug): PromiseInterface
    {
        return $this->getAsyncClient()->getAsync("/markets/slug/$slug")
            ->then(fn (Response $response): array => $response->json());
    }

    /**
     * @param array<string> $marketIds
     */
    public function getMany(array $marketIds, int $concurrency = 10): BatchResult
    {
        $promises = [];
        foreach ($marketIds as $id) {
            $promises[$id] = $this->getAsync($id);
        }

        return $this->getAsyncClient()->pool($promises, $concurrency);
    }

    /**
     * @param array<string> $slugs
     */
    public function getManyBySlug(array $slugs, int $concurrency = 10): BatchResult
    {
        $promises = [];
        foreach ($slugs as $slug) {
            $promises[$slug] = $this->getBySlugAsync($slug);
        }

        return $this->getAsyncClient()->pool($promises, $concurrency);
    }
}
