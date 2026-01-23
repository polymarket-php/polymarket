<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

use Danielgnh\PolymarketPhp\Http\GuzzleHttpClient;
use Danielgnh\PolymarketPhp\Http\HttpClientInterface;
use Danielgnh\PolymarketPhp\Resources\Bridge\Deposits;

/**
 * Bridge API Client.
 *
 * Handles cross-chain deposits from EVM, Solana, and Bitcoin to USDC.e on Polygon.
 * Enables users to fund their Polymarket accounts from multiple blockchains.
 *
 * Resources:
 * - Deposits: Generate deposit addresses and query supported assets
 */
class Bridge
{
    private HttpClientInterface $httpClient;

    /**
     * @param Config                   $config
     * @param HttpClientInterface|null $httpClient
     */
    public function __construct(
        private readonly Config $config,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? new GuzzleHttpClient($this->config->bridgeBaseUrl, $this->config);
    }

    /**
     * Access deposit operations for generating cross-chain deposit addresses.
     *
     * @return Deposits
     */
    public function deposits(): Deposits
    {
        return new Deposits($this->httpClient);
    }
}
