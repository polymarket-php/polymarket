<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Exceptions;

/**
 * Thrown when an async operation is attempted without a configured async client.
 */
class AsyncClientNotConfiguredException extends PolymarketException
{
    public static function notConfigured(): self
    {
        return new self('Async client is not configured. Pass an AsyncClientInterface when constructing the Client.');
    }
}
