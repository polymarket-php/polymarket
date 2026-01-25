<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

use Danielgnh\PolymarketPhp\Http\GuzzleHttpClient;
use Danielgnh\PolymarketPhp\Http\HttpClientInterface;
use Danielgnh\PolymarketPhp\Resources\Data\Activity;
use Danielgnh\PolymarketPhp\Resources\Data\Analytics;
use Danielgnh\PolymarketPhp\Resources\Data\Health;
use Danielgnh\PolymarketPhp\Resources\Data\Leaderboards;
use Danielgnh\PolymarketPhp\Resources\Data\Positions;
use Danielgnh\PolymarketPhp\Resources\Data\Trades;

/**
 * Data API Client.
 *
 * Handles all Data API operations (positions, trades, analytics, leaderboards).
 * https://data-api.polymarket.com
 *
 * Resources:
 * - Health: API health check
 * - Positions: Current and closed positions
 * - Trades: Trade history and activity
 * - Analytics: Market analytics, volume, open interest
 * - Leaderboards: Trader and builder rankings
 */
class Data
{
    private readonly HttpClientInterface $httpClient;

    public function __construct(
        private readonly Config $config,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? new GuzzleHttpClient($this->config->dataBaseUrl, $this->config);
    }

    public function health(): Health
    {
        return new Health($this->httpClient);
    }

    public function positions(): Positions
    {
        return new Positions($this->httpClient);
    }

    public function trades(): Trades
    {
        return new Trades($this->httpClient);
    }

    public function activity(): Activity
    {
        return new Activity($this->httpClient);
    }

    public function analytics(): Analytics
    {
        return new Analytics($this->httpClient);
    }

    public function leaderboards(): Leaderboards
    {
        return new Leaderboards($this->httpClient);
    }
}
