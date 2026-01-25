<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Config;
use Danielgnh\PolymarketPhp\Exceptions\NotFoundException;
use Danielgnh\PolymarketPhp\Http\AsyncClient;
use Danielgnh\PolymarketPhp\Http\BatchResult;
use Danielgnh\PolymarketPhp\Http\Response;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

function createMockGuzzle(array $responses): GuzzleClient
{
    $mock = new MockHandler($responses);
    $handlerStack = HandlerStack::create($mock);

    return new GuzzleClient(['handler' => $handlerStack]);
}

describe('AsyncClient::getAsync', function (): void {
    it('returns a promise that resolves to Response', function (): void {
        $guzzle = createMockGuzzle([
            new GuzzleResponse(200, [], '{"id": "test"}'),
        ]);
        $config = new Config();
        $client = new AsyncClient($guzzle, $config);

        $promise = $client->getAsync('/test');

        expect($promise)->toBeInstanceOf(PromiseInterface::class);

        $response = $promise->wait();
        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toBe(['id' => 'test']);
    });

    it('maps 404 errors to NotFoundException', function (): void {
        $guzzle = createMockGuzzle([
            new RequestException(
                'Not Found',
                new Request('GET', '/test'),
                new GuzzleResponse(404, [], '{"error": "not found"}')
            ),
        ]);
        $config = new Config();
        $client = new AsyncClient($guzzle, $config);

        $promise = $client->getAsync('/test');

        expect(fn () => $promise->wait())->toThrow(NotFoundException::class);
    });
});

describe('AsyncClient::postAsync', function (): void {
    it('sends POST request and returns promise', function (): void {
        $guzzle = createMockGuzzle([
            new GuzzleResponse(200, [], '{"created": true}'),
        ]);
        $config = new Config();
        $client = new AsyncClient($guzzle, $config);

        $promise = $client->postAsync('/test', ['data' => 'value']);
        $response = $promise->wait();

        expect($response->json())->toBe(['created' => true]);
    });
});

describe('AsyncClient::deleteAsync', function (): void {
    it('sends DELETE request and returns promise', function (): void {
        $guzzle = createMockGuzzle([
            new GuzzleResponse(200, [], '{"deleted": true}'),
        ]);
        $config = new Config();
        $client = new AsyncClient($guzzle, $config);

        $promise = $client->deleteAsync('/test/123');
        $response = $promise->wait();

        expect($response->json())->toBe(['deleted' => true]);
    });
});

describe('AsyncClient::pool', function (): void {
    it('executes multiple promises concurrently', function (): void {
        $guzzle = createMockGuzzle([
            new GuzzleResponse(200, [], '{"id": "1"}'),
            new GuzzleResponse(200, [], '{"id": "2"}'),
            new GuzzleResponse(200, [], '{"id": "3"}'),
        ]);
        $config = new Config();
        $client = new AsyncClient($guzzle, $config);

        $promises = [
            'a' => $client->getAsync('/test/1'),
            'b' => $client->getAsync('/test/2'),
            'c' => $client->getAsync('/test/3'),
        ];

        $result = $client->pool($promises);

        expect($result)->toBeInstanceOf(BatchResult::class);
        expect($result->allSucceeded())->toBeTrue();
        expect($result->succeeded)->toHaveCount(3);
    });
});
