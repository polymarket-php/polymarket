<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Exceptions\NotFoundException;
use Danielgnh\PolymarketPhp\Http\BatchResult;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;
use GuzzleHttp\Promise\PromiseInterface;

beforeEach(function (): void {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(gammaHttpClient: $this->fakeHttp);
});

describe('Markets::getAsync', function (): void {
    it('returns a promise that resolves to market data', function (): void {
        $marketData = $this->loadFixture('market.json');
        $this->fakeHttp->addJsonResponse('GET', '/markets/test-id', $marketData);

        $promise = $this->client->gamma()->markets()->getAsync('test-id');

        expect($promise)->toBeInstanceOf(PromiseInterface::class);

        $result = $promise->wait();
        expect($result)->toBe($marketData);
    });
});

describe('Markets::listAsync', function (): void {
    it('returns a promise that resolves to market list', function (): void {
        $marketsData = $this->loadFixture('markets_list.json');
        $this->fakeHttp->addJsonResponse('GET', '/markets', $marketsData);

        $promise = $this->client->gamma()->markets()->listAsync();

        expect($promise)->toBeInstanceOf(PromiseInterface::class);

        $result = $promise->wait();
        expect($result)->toBe($marketsData);
    });
});

describe('Markets::getBySlugAsync', function (): void {
    it('returns a promise that resolves to market data', function (): void {
        $marketData = $this->loadFixture('market.json');
        $this->fakeHttp->addJsonResponse('GET', '/markets/slug/test-slug', $marketData);

        $promise = $this->client->gamma()->markets()->getBySlugAsync('test-slug');
        $result = $promise->wait();

        expect($result)->toBe($marketData);
    });
});

describe('Markets::getMany', function (): void {
    it('fetches multiple markets in parallel', function (): void {
        $market1 = ['id' => 1, 'question' => 'Question 1'];
        $market2 = ['id' => 2, 'question' => 'Question 2'];
        $market3 = ['id' => 3, 'question' => 'Question 3'];

        $this->fakeHttp->addJsonResponse('GET', '/markets/id1', $market1);
        $this->fakeHttp->addJsonResponse('GET', '/markets/id2', $market2);
        $this->fakeHttp->addJsonResponse('GET', '/markets/id3', $market3);

        $result = $this->client->gamma()->markets()->getMany(['id1', 'id2', 'id3']);

        expect($result)->toBeInstanceOf(BatchResult::class)
            ->and($result->allSucceeded())->toBeTrue()
            ->and($result)->toHaveCount(3)
            ->and($result['id1'])->toBe($market1)
            ->and($result['id2'])->toBe($market2);
    });

    it('handles partial failures', function (): void {
        $market1 = ['id' => 1, 'question' => 'Question 1'];

        $this->fakeHttp->addJsonResponse('GET', '/markets/id1', $market1);
        $this->fakeHttp->addExceptionResponse('GET', '/markets/id2', new NotFoundException('Not found'));

        $result = $this->client->gamma()->markets()->getMany(['id1', 'id2']);

        expect($result->hasFailures())->toBeTrue()
            ->and($result->succeeded)->toHaveCount(1)
            ->and($result->failed)->toHaveCount(1)
            ->and($result->failed['id2'])->toBeInstanceOf(NotFoundException::class);
    });

    it('iterates over succeeded results', function (): void {
        $this->fakeHttp->addJsonResponse('GET', '/markets/id1', ['id' => 1]);
        $this->fakeHttp->addJsonResponse('GET', '/markets/id2', ['id' => 2]);

        $result = $this->client->gamma()->markets()->getMany(['id1', 'id2']);

        $ids = [];
        foreach ($result as $key => $market) {
            $ids[] = $key;
        }

        expect($ids)->toBe(['id1', 'id2']);
    });
});

describe('Markets::getManyBySlug', function (): void {
    it('fetches multiple markets by slug in parallel', function (): void {
        $market1 = ['id' => 1, 'slug' => 'slug1'];
        $market2 = ['id' => 2, 'slug' => 'slug2'];

        $this->fakeHttp->addJsonResponse('GET', '/markets/slug/slug1', $market1);
        $this->fakeHttp->addJsonResponse('GET', '/markets/slug/slug2', $market2);

        $result = $this->client->gamma()->markets()->getManyBySlug(['slug1', 'slug2']);

        expect($result)->toBeInstanceOf(BatchResult::class)
            ->and($result->allSucceeded())->toBeTrue()
            ->and($result['slug1'])->toBe($market1);
    });
});
