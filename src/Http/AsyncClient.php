<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Http;

use Danielgnh\PolymarketPhp\Config;
use Danielgnh\PolymarketPhp\Exceptions\ApiException;
use Danielgnh\PolymarketPhp\Exceptions\AuthenticationException;
use Danielgnh\PolymarketPhp\Exceptions\NotFoundException;
use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Exceptions\RateLimitException;
use Danielgnh\PolymarketPhp\Exceptions\ValidationException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class AsyncClient implements AsyncClientInterface
{
    private readonly RequestPool $pool;

    public function __construct(
        private readonly GuzzleClient $guzzle,
        private readonly Config $config,
    ) {
        $this->pool = new RequestPool($this->config->defaultConcurrency);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getAsync(string $path, array $query = []): PromiseInterface
    {
        return $this->requestAsync('GET', $path, ['query' => $query]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function postAsync(string $path, array $data = []): PromiseInterface
    {
        return $this->requestAsync('POST', $path, ['json' => $data]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function deleteAsync(string $path, array $data = []): PromiseInterface
    {
        $options = $data === [] ? [] : ['json' => $data];

        return $this->requestAsync('DELETE', $path, $options);
    }

    /**
     * @param array<string, PromiseInterface> $promises
     */
    public function pool(array $promises, ?int $concurrency = null): BatchResult
    {
        return $this->pool->batch($promises, $concurrency);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function requestAsync(string $method, string $path, array $options = []): PromiseInterface
    {
        $options['timeout'] = $this->config->asyncTimeout;

        return $this->guzzle->requestAsync($method, $path, $options)
            ->then(
                fn (ResponseInterface $response): Response => $this->createResponse($response),
                fn (\Throwable $e) => throw $this->mapException($e)
            );
    }

    private function createResponse(ResponseInterface $response): Response
    {
        /** @var array<string, array<string>> $headers */
        $headers = $response->getHeaders();

        return new Response(
            statusCode: $response->getStatusCode(),
            headers: $this->normalizeHeaders($headers),
            body: $response->getBody()->getContents()
        );
    }

    /**
     * @param array<string, array<string>> $headers
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        return array_map(fn (array $values): string => implode(', ', $values), $headers);
    }

    private function mapException(\Throwable $e): PolymarketException
    {
        if (!$e instanceof RequestException) {
            return new PolymarketException($e->getMessage(), (int) $e->getCode());
        }

        $code = $e->getCode();
        $message = $e->getMessage();

        return match (true) {
            $code === 401 || $code === 403 => new AuthenticationException($message, $code),
            $code === 404 => new NotFoundException($message, $code),
            $code === 422 || $code === 400 => new ValidationException($message, $code),
            $code === 429 => new RateLimitException($message, $code),
            $code >= 500 => new ApiException('Server error: ' . $message, $code),
            default => new PolymarketException($message, $code),
        };
    }
}
