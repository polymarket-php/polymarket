<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Config;

it('creates config with default values', function (): void {
    $config = new Config();

    expect($config->apiKey)->toBeNull()
        ->and($config->gammaBaseUrl)->toBe('https://gamma-api.polymarket.com')
        ->and($config->clobBaseUrl)->toBe('https://clob.polymarket.com')
        ->and($config->timeout)->toBe(30)
        ->and($config->retries)->toBe(3)
        ->and($config->verifySSL)->toBeTrue();
});

it('creates config with api key', function (): void {
    $config = new Config('test-api-key');

    expect($config->apiKey)->toBe('test-api-key');
});

it('allows custom gamma base url', function (): void {
    $config = new Config(null, ['gamma_base_url' => 'https://custom-gamma.example.com']);

    expect($config->gammaBaseUrl)->toBe('https://custom-gamma.example.com');
});

it('allows custom clob base url', function (): void {
    $config = new Config(null, ['clob_base_url' => 'https://custom-clob.example.com']);

    expect($config->clobBaseUrl)->toBe('https://custom-clob.example.com');
});

it('allows custom timeout', function (): void {
    $config = new Config(null, ['timeout' => 60]);

    expect($config->timeout)->toBe(60);
});

it('allows custom retries', function (): void {
    $config = new Config(null, ['retries' => 5]);

    expect($config->retries)->toBe(5);
});

it('allows disabling ssl verification', function (): void {
    $config = new Config(null, ['verify_ssl' => false]);

    expect($config->verifySSL)->toBeFalse();
});

it('accepts multiple options at once', function (): void {
    $config = new Config('my-key', [
        'gamma_base_url' => 'https://test-gamma.com',
        'clob_base_url' => 'https://test-clob.com',
        'timeout' => 45,
        'retries' => 2,
        'verify_ssl' => false,
    ]);

    expect($config->apiKey)->toBe('my-key')
        ->and($config->gammaBaseUrl)->toBe('https://test-gamma.com')
        ->and($config->clobBaseUrl)->toBe('https://test-clob.com')
        ->and($config->timeout)->toBe(45)
        ->and($config->retries)->toBe(2)
        ->and($config->verifySSL)->toBeFalse();
});

describe('Config concurrency options', function (): void {
    it('uses default concurrency values', function (): void {
        $config = new Config();

        expect($config->defaultConcurrency)->toBe(10);
        expect($config->asyncTimeout)->toBe(30);
    });

    it('accepts custom concurrency values', function (): void {
        $config = new Config(null, [
            'default_concurrency' => 20,
            'async_timeout' => 60,
        ]);

        expect($config->defaultConcurrency)->toBe(20);
        expect($config->asyncTimeout)->toBe(60);
    });
});
