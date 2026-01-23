<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Bridge;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

/**
 * Bridge Deposits Resource.
 *
 * Handles cross-chain deposit address generation and asset queries.
 * Supports EVM (Ethereum), Solana (SVM), and Bitcoin blockchains.
 */
class Deposits extends Resource
{
    /**
     * Generate deposit addresses for bridging assets to Polygon USDC.e.
     *
     * Returns deposit addresses for each supported chain type (EVM, Solana, Bitcoin).
     * Users can send assets to these addresses, which will be automatically bridged
     * to USDC.e on Polygon for trading on Polymarket.
     *
     * @param array<string, mixed> $depositData Deposit request data with the following structure:
     *                                          - destination_address (string): Target Polygon address for USDC.e
     *                                          - amount_usd (string): Deposit amount in USD
     *
     * @return array<string, mixed> Deposit addresses for each chain
     *                              {
     *                              "evm": "0x...",
     *                              "solana": "...",
     *                              "bitcoin": "..."
     *                              }
     *
     * @throws PolymarketException
     */
    public function generate(array $depositData): array
    {
        $response = $this->httpClient->post('/deposit', $depositData);

        return $response->json();
    }

    /**
     * Get available chains and supported tokens for bridging.
     *
     * Returns information about:
     * - Supported blockchain networks (EVM chains, Solana, Bitcoin)
     * - Available tokens on each chain
     * - Minimum deposit thresholds in USD
     * - Chain IDs and network details
     *
     * @return array<string, mixed> Supported assets and chains
     *                              {
     *                              "chains": [...],
     *                              "tokens": [...],
     *                              "minimums": {...}
     *                              }
     *
     * @throws PolymarketException
     */
    public function supportedAssets(): array
    {
        $response = $this->httpClient->get('/supported-assets');

        return $response->json();
    }
}
