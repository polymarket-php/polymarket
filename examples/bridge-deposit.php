<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Danielgnh\PolymarketPhp\Client;

/**
 * Example: Cross-Chain Bridge Deposits.
 *
 * This example demonstrates how to use the Bridge API to:
 * - Query supported assets and chains
 * - Generate deposit addresses for multiple blockchains (EVM, Solana, Bitcoin)
 * - Fund Polymarket account from various chains
 *
 * The Bridge API enables users to deposit funds from:
 * - EVM chains (Ethereum, Arbitrum, Base, etc.)
 * - Solana (SVM)
 * - Bitcoin
 *
 * All deposits are automatically converted to USDC.e on Polygon
 * for trading on Polymarket.
 */

$client = new Client();

echo "Bridge API Examples\n";
echo "==================\n\n";

// 1. Get supported assets and chains
try {
    echo "1. Fetching supported assets and chains...\n";
    $supportedAssets = $client->bridge()->deposits()->supportedAssets();

    echo "Supported Chains:\n";
    if (isset($supportedAssets['chains'])) {
        foreach ($supportedAssets['chains'] as $chain) {
            echo "  - {$chain['name']} (Chain ID: {$chain['id']})\n";
        }
    }

    echo "\nSupported Tokens:\n";
    if (isset($supportedAssets['tokens'])) {
        foreach ($supportedAssets['tokens'] as $token) {
            echo "  - {$token['symbol']}: {$token['name']}\n";
            echo "    Min deposit: \${$token['minimum_usd']}\n";
        }
    }

    echo "\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// 2. Generate deposit addresses for multi-chain deposits
try {
    echo "2. Generating deposit addresses...\n";

    $destinationAddress = null; // Use your address here, be accurate.
    $amount = '100'; // Amount to deposit in USD

    $depositAddresses = $client->bridge()->deposits()->generate([
        'destination_address' => $destinationAddress,
        'amount_usd' => $amount,
    ]);

    echo "\nDeposit Addresses:\n";
    echo "Send assets to any of these addresses to fund your Polymarket account:\n\n";

    // EVM chains (Ethereum, Arbitrum, Base, etc.)
    if (isset($depositAddresses['evm'])) {
        echo "EVM Chains (Ethereum, Arbitrum, Base, etc.):\n";
        echo "  Address: {$depositAddresses['evm']}\n";
        echo "  Tokens: USDC, USDT, ETH, etc.\n";
        echo "  Note: Gas fees apply on source chain\n\n";
    }

    // Solana
    if (isset($depositAddresses['solana'])) {
        echo "Solana:\n";
        echo "  Address: {$depositAddresses['solana']}\n";
        echo "  Tokens: USDC (SPL), SOL\n";
        echo "  Note: Low gas fees\n\n";
    }

    if (isset($depositAddresses['bitcoin'])) {
        echo "Bitcoin:\n";
        echo "  Address: {$depositAddresses['bitcoin']}\n";
        echo "  Token: BTC\n";
        echo "  Note: Longer confirmation times\n\n";
    }

    echo "Destination: $destinationAddress (Polygon)\n";
    echo "All deposits will be converted to USDC.e on Polygon\n\n";
} catch (Exception $e) {
    echo "Error generating deposit addresses: {$e->getMessage()}\n\n";
}