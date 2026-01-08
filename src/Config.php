<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

class Config
{
    public readonly string $gammaBaseUrl;

    public readonly string $clobBaseUrl;

    public readonly string $bridgeBaseUrl;

    public readonly string $dataBaseUrl;

    public readonly ?string $apiKey;

    public readonly int $timeout;

    public readonly int $retries;

    public readonly bool $verifySSL;

    public ?string $privateKey;

    public int $chainId;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(?string $apiKey = null, array $options = [])
    {
        $apiKeyValue = $apiKey ?? ($_ENV['POLYMARKET_API_KEY'] ?? null);
        $this->apiKey = is_string($apiKeyValue) ? $apiKeyValue : null;
        $this->gammaBaseUrl = is_string($options['gamma_base_url'] ?? null) ? $options['gamma_base_url'] : 'https://gamma-api.polymarket.com';
        $this->clobBaseUrl = is_string($options['clob_base_url'] ?? null) ? $options['clob_base_url'] : 'https://clob.polymarket.com';
        $this->bridgeBaseUrl = is_string($options['bridge_base_url'] ?? null) ? $options['bridge_base_url'] : 'https://bridge-api.polymarket.com';
	    $this->dataBaseUrl = is_string($options['data_base_url'] ?? null) ? $options['data_base_url'] : 'https://data-api.polymarket.com';
		$this->timeout = is_int($options['timeout'] ?? null) ? $options['timeout'] : 30;
        $this->retries = is_int($options['retries'] ?? null) ? $options['retries'] : 3;
        $this->verifySSL = is_bool($options['verify_ssl'] ?? null) ? $options['verify_ssl'] : true;
        $privateKeyValue = $options['private_key'] ?? ($_ENV['POLYMARKET_PRIVATE_KEY'] ?? null);
        $this->privateKey = is_string($privateKeyValue) ? $privateKeyValue : null;
        $this->chainId = is_int($options['chain_id'] ?? null) ? $options['chain_id'] : 137;
    }
}
