<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(bridgeHttpClient: $this->fakeHttp);
});

describe('Deposits::supportedAssets()', function () {
    it('fetches supported assets and chains successfully', function () {
        $supportedAssetsData = [
            'chains' => [
                [
                    'id' => 1,
                    'name' => 'Ethereum',
                    'type' => 'evm',
                ],
                [
                    'id' => 42161,
                    'name' => 'Arbitrum',
                    'type' => 'evm',
                ],
                [
                    'id' => 0,
                    'name' => 'Solana',
                    'type' => 'solana',
                ],
            ],
            'tokens' => [
                [
                    'symbol' => 'USDC',
                    'name' => 'USD Coin',
                    'minimum_usd' => '10',
                ],
                [
                    'symbol' => 'USDT',
                    'name' => 'Tether USD',
                    'minimum_usd' => '10',
                ],
                [
                    'symbol' => 'ETH',
                    'name' => 'Ethereum',
                    'minimum_usd' => '10',
                ],
            ],
            'minimums' => [
                'usd' => '10',
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/supported-assets', $supportedAssetsData);

        $result = $this->client->bridge()->deposits()->supportedAssets();

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('chains')
            ->and($result)->toHaveKey('tokens')
            ->and($result)->toHaveKey('minimums')
            ->and($result['chains'])->toHaveCount(3)
            ->and($result['tokens'])->toHaveCount(3)
            ->and($result['chains'][0]['name'])->toBe('Ethereum')
            ->and($result['tokens'][0]['symbol'])->toBe('USDC');
    });

    it('handles empty supported assets response', function () {
        $supportedAssetsData = [
            'chains' => [],
            'tokens' => [],
            'minimums' => [],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/supported-assets', $supportedAssetsData);

        $result = $this->client->bridge()->deposits()->supportedAssets();

        expect($result)->toBeArray()
            ->and($result['chains'])->toBeEmpty()
            ->and($result['tokens'])->toBeEmpty();
    });

    it('returns chain details correctly', function () {
        $supportedAssetsData = [
            'chains' => [
                [
                    'id' => 8453,
                    'name' => 'Base',
                    'type' => 'evm',
                    'network' => 'mainnet',
                ],
            ],
            'tokens' => [],
            'minimums' => [],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/supported-assets', $supportedAssetsData);

        $result = $this->client->bridge()->deposits()->supportedAssets();

        expect($result['chains'][0])->toHaveKey('id')
            ->and($result['chains'][0])->toHaveKey('name')
            ->and($result['chains'][0])->toHaveKey('type')
            ->and($result['chains'][0]['id'])->toBe(8453)
            ->and($result['chains'][0]['type'])->toBe('evm');
    });
});

describe('Deposits::generate()', function () {
    it('generates deposit addresses successfully', function () {
        $depositAddressData = [
            'evm' => '0x1234567890abcdef1234567890abcdef12345678',
            'solana' => 'SomeBase58SolanaAddress123456789',
            'bitcoin' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
        ];

        $this->fakeHttp->addJsonResponse('POST', '/deposit', $depositAddressData);

        $result = $this->client->bridge()->deposits()->generate([
            'destination_address' => '0xdeadbeef00000000000000000000000000000000',
            'amount_usd' => '100',
        ]);

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('evm')
            ->and($result)->toHaveKey('solana')
            ->and($result)->toHaveKey('bitcoin')
            ->and($result['evm'])->toBe('0x1234567890abcdef1234567890abcdef12345678')
            ->and($result['solana'])->toBe('SomeBase58SolanaAddress123456789')
            ->and($result['bitcoin'])->toBe('bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh');
    });

    it('passes correct parameters to API', function () {
        $depositData = [
            'destination_address' => '0xabcdef1234567890abcdef1234567890abcdef12',
            'amount_usd' => '250',
        ];

        $depositAddressData = [
            'evm' => '0x1234567890abcdef1234567890abcdef12345678',
            'solana' => 'SomeBase58SolanaAddress123456789',
            'bitcoin' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
        ];

        $this->fakeHttp->addJsonResponse('POST', '/deposit', $depositAddressData);

        $result = $this->client->bridge()->deposits()->generate($depositData);

        expect($result)->toBeArray()
            ->and($this->fakeHttp->hasRequest('POST', '/deposit'))->toBeTrue();
    });

    it('handles partial address generation', function () {
        // Sometimes not all chains may be available
        $depositAddressData = [
            'evm' => '0x1234567890abcdef1234567890abcdef12345678',
            'solana' => 'SomeBase58SolanaAddress123456789',
        ];

        $this->fakeHttp->addJsonResponse('POST', '/deposit', $depositAddressData);

        $result = $this->client->bridge()->deposits()->generate([
            'destination_address' => '0xdeadbeef00000000000000000000000000000000',
            'amount_usd' => '50',
        ]);

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('evm')
            ->and($result)->toHaveKey('solana')
            ->and($result)->not->toHaveKey('bitcoin');
    });

    it('handles minimum amount requirements', function () {
        $depositAddressData = [
            'evm' => '0x1234567890abcdef1234567890abcdef12345678',
            'solana' => 'SomeBase58SolanaAddress123456789',
            'bitcoin' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
        ];

        $this->fakeHttp->addJsonResponse('POST', '/deposit', $depositAddressData);

        $result = $this->client->bridge()->deposits()->generate([
            'destination_address' => '0xdeadbeef00000000000000000000000000000000',
            'amount_usd' => '10',
        ]);

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('evm');
    });

    it('handles different deposit amounts', function () {
        $depositAddressData = [
            'evm' => '0x1234567890abcdef1234567890abcdef12345678',
            'solana' => 'SomeBase58SolanaAddress123456789',
            'bitcoin' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
        ];

        $this->fakeHttp->addJsonResponse('POST', '/deposit', $depositAddressData);

        $result = $this->client->bridge()->deposits()->generate([
            'destination_address' => '0xdeadbeef00000000000000000000000000000000',
            'amount_usd' => '10000',
        ]);

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('evm')
            ->and($result)->toHaveKey('solana')
            ->and($result)->toHaveKey('bitcoin');
    });
});
