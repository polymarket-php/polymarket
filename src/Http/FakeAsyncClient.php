<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Http;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;

/**
 * Fake async client for testing purposes.
 * Allows setting up predefined async responses without making real HTTP calls.
 */
class FakeAsyncClient implements AsyncClientInterface
{
    /** @var array<string, Response> */
    private array $responses = [];

    /** @var array<string, array{method: string, path: string, data: array<int|string, mixed>}> */
    private array $requests = [];

    /** @var array<string, PolymarketException> */
    private array $exceptions = [];

    private readonly RequestPool $pool;

    public function __construct()
    {
        $this->pool = new RequestPool();
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getAsync(string $path, array $query = []): PromiseInterface
    {
        return $this->requestAsync('GET', $path, $query);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function postAsync(string $path, array $data = []): PromiseInterface
    {
        return $this->requestAsync('POST', $path, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function deleteAsync(string $path, array $data = []): PromiseInterface
    {
        return $this->requestAsync('DELETE', $path, $data);
    }

    /**
     * @param array<string, PromiseInterface> $promises
     */
    public function pool(array $promises, ?int $concurrency = null): BatchResult
    {
        return $this->pool->batch($promises, $concurrency);
    }

    /**
     * Set a response for a specific method and path.
     */
    public function addResponse(string $method, string $path, Response $response): void
    {
        $key = $this->makeKey($method, $path);
        $this->responses[$key] = $response;
    }

    /**
     * Set a JSON response for a specific method and path.
     *
     * @param array<string, mixed> $data
     *
     * @throws PolymarketException
     */
    public function addJsonResponse(string $method, string $path, array $data, int $statusCode = 200): void
    {
        $body = json_encode($data);

        if ($body === false) {
            throw new PolymarketException('Failed to encode JSON response: ' . json_last_error_msg());
        }

        $response = new Response(
            statusCode: $statusCode,
            headers: ['Content-Type' => 'application/json'],
            body: $body
        );

        $this->addResponse($method, $path, $response);
    }

    public function addExceptionResponse(string $method, string $path, PolymarketException $exception): void
    {
        $key = $this->makeKey($method, $path);
        $this->exceptions[$key] = $exception;
    }

    /**
     * Check if a specific request was made.
     */
    public function hasRequest(string $method, string $path): bool
    {
        $key = $this->makeKey($method, $path);

        return isset($this->requests[$key]);
    }

    /**
     * @param array<int|string, mixed> $data
     */
    private function requestAsync(string $method, string $path, array $data): PromiseInterface
    {
        $this->recordRequest($method, $path, $data);
        $key = $this->makeKey($method, $path);

        if (isset($this->exceptions[$key])) {
            return new RejectedPromise($this->exceptions[$key]);
        }

        return new FulfilledPromise($this->getResponse($method, $path));
    }

    /**
     * @param array<int|string, mixed> $data
     */
    private function recordRequest(string $method, string $path, array $data): void
    {
        $key = $this->makeKey($method, $path);
        $this->requests[$key] = [
            'method' => $method,
            'path' => $path,
            'data' => $data,
        ];
    }

    /**
     * @throws PolymarketException
     */
    private function getResponse(string $method, string $path): Response
    {
        $key = $this->makeKey($method, $path);

        if (!isset($this->responses[$key])) {
            $body = json_encode([
                'error' => 'Not Found',
                'message' => "No fake response set for [$method $path]",
            ]);

            if ($body === false) {
                throw new PolymarketException('Failed to encode JSON response: ' . json_last_error_msg());
            }

            return new Response(
                statusCode: 404,
                headers: ['Content-Type' => 'application/json'],
                body: $body
            );
        }

        return $this->responses[$key];
    }

    private function makeKey(string $method, string $path): string
    {
        return strtoupper($method) . ' ' . $path;
    }
}
