<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Clob;

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
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function list(array $params = []): array
    {
        return $this->httpClient->get('/markets', $params)->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getSimplified(array $params = []): array
    {
        return $this->httpClient->get('/simplified-markets', $params)->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getSampling(array $params = []): array
    {
        return $this->httpClient->get('/sampling-markets', $params)->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getSamplingSimplified(array $params = []): array
    {
        return $this->httpClient->get('/sampling-simplified-markets', $params)->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $conditionId): array
    {
        return $this->httpClient->get("/market/{$conditionId}")->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getTradeEvents(string $conditionId): array
    {
        return $this->httpClient->get("/market-trades-events/{$conditionId}")->json();
    }

    /**
     * @param array<string, mixed> $params
     */
    public function listAsync(array $params = []): PromiseInterface
    {
        return $this->getAsyncClient()->getAsync('/markets', $params)
            ->then(fn (Response $response): array => $response->json());
    }

    public function getAsync(string $conditionId): PromiseInterface
    {
        return $this->getAsyncClient()->getAsync("/market/{$conditionId}")
            ->then(fn (Response $response): array => $response->json());
    }

    /**
     * @param array<string> $conditionIds
     */
    public function getMany(array $conditionIds, int $concurrency = 10): BatchResult
    {
        $promises = [];
        foreach ($conditionIds as $id) {
            $promises[$id] = $this->getAsync($id);
        }

        return $this->getAsyncClient()->pool($promises, $concurrency);
    }
}
