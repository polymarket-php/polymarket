<?php

declare(strict_types=1);

use PolymarketPhp\Polymarket\Client;
use PolymarketPhp\Polymarket\Http\FakeGuzzleHttpClient;

beforeEach(function (): void {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(gammaHttpClient: $this->fakeHttp, clobHttpClient: $this->fakeHttp);
});

describe('Series::list()', function (): void {
    it('lists series', function (): void {
        $seriesData = [
            [
                'id' => 'series1',
                'title' => 'NBA Finals 2024',
                'description' => 'Markets for the NBA Finals',
            ],
            [
                'id' => 'series2',
                'title' => 'World Cup 2026',
                'description' => 'World Cup prediction markets',
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/series', $seriesData);

        $result = $this->client->gamma()->series()->list();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0]['title'])->toBe('NBA Finals 2024');
    });

    it('applies custom filters', function (): void {
        $seriesData = $this->loadFixture('series_list.json');
        $filteredData = array_filter($seriesData, fn ($s): bool => 'abc' === $s['slug']);

        $this->fakeHttp->addJsonResponse('GET', '/series', array_values($filteredData));

        $result = $this->client->gamma()->series()->list(filters: ['slug' => 'abc']);

        expect($result)->toBeArray()
            ->and(count($result))->toBe(1);

        foreach ($result as $market) {
            expect($market['slug'])->toBe('abc');
        }
    });

    it('handles empty series list', function (): void {
        $this->fakeHttp->addJsonResponse('GET', '/series', []);

        $result = $this->client->gamma()->series()->list();

        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });
});

describe('Series::get()', function (): void {
    it('gets series by ID', function (): void {
        $seriesData = [
            'id' => 'series1',
            'title' => 'NBA Finals 2024',
            'description' => 'Markets for the NBA Finals',
            'markets' => ['market1', 'market2', 'market3'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/series/series1', $seriesData);

        $result = $this->client->gamma()->series()->get('series1');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('series1')
            ->and($result['title'])->toBe('NBA Finals 2024')
            ->and($result['markets'])->toHaveCount(3);
    });
});
