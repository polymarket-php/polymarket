<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Http;

use GuzzleHttp\Promise\PromiseInterface;

interface AsyncClientInterface
{
    /**
     * @param array<string, mixed> $query
     */
    public function getAsync(string $path, array $query = []): PromiseInterface;

    /**
     * @param array<string, mixed> $data
     */
    public function postAsync(string $path, array $data = []): PromiseInterface;

    /**
     * @param array<string, mixed> $data
     */
    public function deleteAsync(string $path, array $data = []): PromiseInterface;

    /**
     * @param array<string, PromiseInterface> $promises
     */
    public function pool(array $promises, ?int $concurrency = null): BatchResult;
}
