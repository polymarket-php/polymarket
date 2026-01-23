<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

use Danielgnh\PolymarketPhp\Auth\ClobAuthenticator;
use Danielgnh\PolymarketPhp\Auth\Signer\Eip712Signer;
use Danielgnh\PolymarketPhp\Exceptions\ClobAuthenticationException;
use Danielgnh\PolymarketPhp\Exceptions\SigningException;
use Danielgnh\PolymarketPhp\Http\HttpClientInterface;

class Client
{
    private Config $config;

    private ?Gamma $gammaClient = null;

    private ?Clob $clobClient = null;

    private ?Bridge $bridgeClient = null;

    private ?ClobAuthenticator $clobAuthenticator = null;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        ?string $apiKey = null,
        array $options = [],
        ?HttpClientInterface $gammaHttpClient = null,
        ?HttpClientInterface $clobHttpClient = null,
        ?HttpClientInterface $bridgeHttpClient = null
    ) {
        $this->config = new Config($apiKey, $options);

        if ($gammaHttpClient !== null) {
            $this->gammaClient = new Gamma($this->config, $gammaHttpClient);
        }

        if ($clobHttpClient !== null) {
            $this->clobClient = new Clob($this->config, $clobHttpClient);
        }

        if ($bridgeHttpClient !== null) {
            $this->bridgeClient = new Bridge($this->config, $bridgeHttpClient);
        }
    }

    /**
     * Setup CLOB authentication using private key.
     *
     * This must be called before CLOB write operations.
     *
     * @param string|null $privateKey Hex private key (0x...).
     * @param int         $nonce Nonce for credential derivation (default: 0)
     *
     * @throws ClobAuthenticationException
     * @throws SigningException
     */
    public function auth(
        ?string $privateKey = null,
        int $nonce = 0
    ): void {
        $key = $privateKey ?? $this->config->privateKey;

        if ($key === null) {
            throw ClobAuthenticationException::missingPrivateKey();
        }

        $signer = new Eip712Signer($key, $this->config->chainId);

        $this->clobAuthenticator = new ClobAuthenticator(
            $signer,
            $this->config->clobBaseUrl,
            $this->config->chainId
        );

        $credentials = $this->clobAuthenticator->deriveOrCreateCredentials($nonce);
        $this->clobAuthenticator = $this->clobAuthenticator->withCredentials($credentials);

        $this->clobClient?->auth($this->clobAuthenticator);
    }

    public function gamma(): Gamma
    {
        if ($this->gammaClient === null) {
            $this->gammaClient = new Gamma($this->config);
        }

        return $this->gammaClient;
    }

    public function clob(): Clob
    {
        if ($this->clobClient === null) {
            $this->clobClient = new Clob(
                $this->config,
                null,
                $this->clobAuthenticator
            );
        }

        return $this->clobClient;
    }

    /**
     * Get Bridge API client for cross-chain deposits.
     *
     * @return Bridge
     */
    public function bridge(): Bridge
    {
        if ($this->bridgeClient === null) {
            $this->bridgeClient = new Bridge($this->config);
        }

        return $this->bridgeClient;
    }
}
