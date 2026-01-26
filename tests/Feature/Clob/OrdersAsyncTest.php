<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Exceptions\AsyncClientNotConfiguredException;
use Danielgnh\PolymarketPhp\Exceptions\NotFoundException;
use Danielgnh\PolymarketPhp\Http\BatchResult;
use Danielgnh\PolymarketPhp\Http\FakeAsyncClient;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;
use GuzzleHttp\Promise\PromiseInterface;

beforeEach(function (): void {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->fakeAsync = new FakeAsyncClient();
    $this->client = new Client(clobHttpClient: $this->fakeHttp, clobAsyncClient: $this->fakeAsync);
});

describe('Orders::getAsync', function (): void {
    it('returns a promise that resolves to order data', function (): void {
        $orderData = ['id' => 'order-1', 'status' => 'open'];
        $this->fakeAsync->addJsonResponse('GET', '/data/order/order-1', $orderData);

        $promise = $this->client->clob()->orders()->getAsync('order-1');

        expect($promise)->toBeInstanceOf(PromiseInterface::class);

        $result = $promise->wait();
        expect($result)->toBe($orderData);
    });
});

describe('Orders::listAsync', function (): void {
    it('returns a promise that resolves to orders list', function (): void {
        $ordersData = ['orders' => [], 'next_cursor' => ''];
        $this->fakeAsync->addJsonResponse('GET', '/data/orders', $ordersData);

        $promise = $this->client->clob()->orders()->listAsync();

        expect($promise)->toBeInstanceOf(PromiseInterface::class);

        $result = $promise->wait();
        expect($result)->toBe($ordersData);
    });
});

describe('Orders::getOpenAsync', function (): void {
    it('returns a promise that resolves to open orders', function (): void {
        $ordersData = ['orders' => []];
        $this->fakeAsync->addJsonResponse('GET', '/open-orders', $ordersData);

        $promise = $this->client->clob()->orders()->getOpenAsync();
        $result = $promise->wait();

        expect($result)->toBe($ordersData);
    });
});

describe('Orders::getMany', function (): void {
    it('fetches multiple orders in parallel', function (): void {
        $order1 = ['id' => 'id1', 'status' => 'open'];
        $order2 = ['id' => 'id2', 'status' => 'filled'];

        $this->fakeAsync->addJsonResponse('GET', '/data/order/id1', $order1);
        $this->fakeAsync->addJsonResponse('GET', '/data/order/id2', $order2);

        $result = $this->client->clob()->orders()->getMany(['id1', 'id2']);

        expect($result)->toBeInstanceOf(BatchResult::class);
        expect($result->allSucceeded())->toBeTrue();
        expect($result['id1'])->toBe($order1);
        expect($result['id2'])->toBe($order2);
    });

    it('handles partial failures', function (): void {
        $this->fakeAsync->addJsonResponse('GET', '/data/order/id1', ['id' => 'id1']);
        $this->fakeAsync->addExceptionResponse('GET', '/data/order/id2', new NotFoundException('Not found'));

        $result = $this->client->clob()->orders()->getMany(['id1', 'id2']);

        expect($result->hasFailures())->toBeTrue();
        expect($result->succeeded)->toHaveCount(1);
        expect($result->failed)->toHaveCount(1);
    });
});

describe('Orders::cancelMany', function (): void {
    it('cancels multiple orders in parallel', function (): void {
        $result1 = ['id' => 'id1', 'cancelled' => true];
        $result2 = ['id' => 'id2', 'cancelled' => true];

        $this->fakeAsync->addJsonResponse('DELETE', '/orders/id1', $result1);
        $this->fakeAsync->addJsonResponse('DELETE', '/orders/id2', $result2);

        $result = $this->client->clob()->orders()->cancelMany(['id1', 'id2']);

        expect($result)->toBeInstanceOf(BatchResult::class);
        expect($result->allSucceeded())->toBeTrue();
        expect($result['id1'])->toBe($result1);
    });

    it('handles partial failures when cancelling', function (): void {
        $this->fakeAsync->addJsonResponse('DELETE', '/orders/id1', ['cancelled' => true]);
        $this->fakeAsync->addExceptionResponse('DELETE', '/orders/id2', new NotFoundException('Order not found'));

        $result = $this->client->clob()->orders()->cancelMany(['id1', 'id2']);

        expect($result->hasFailures())->toBeTrue();
        expect($result->succeeded)->toHaveCount(1);
        expect($result->failed)->toHaveCount(1);
    });

    it('uses lower default concurrency for write operations', function (): void {
        $this->fakeAsync->addJsonResponse('DELETE', '/orders/id1', ['cancelled' => true]);

        $result = $this->client->clob()->orders()->cancelMany(['id1'], concurrency: 3);

        expect($result->allSucceeded())->toBeTrue();
    });
});

describe('Orders async without async client', function (): void {
    it('throws AsyncClientNotConfiguredException when async client is not configured', function (): void {
        $httpOnly = new FakeGuzzleHttpClient();
        $client = new Client(clobHttpClient: $httpOnly);

        $client->clob()->orders()->getAsync('order-1');
    })->throws(AsyncClientNotConfiguredException::class);
});
