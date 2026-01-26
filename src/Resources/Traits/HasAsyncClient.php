<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Traits;

use Danielgnh\PolymarketPhp\Exceptions\AsyncClientNotConfiguredException;
use Danielgnh\PolymarketPhp\Http\AsyncClientInterface;

trait HasAsyncClient
{
    /**
     * @throws AsyncClientNotConfiguredException
     */
    private function getAsyncClient(): AsyncClientInterface
    {
        if (!$this->asyncClient instanceof AsyncClientInterface) {
            throw AsyncClientNotConfiguredException::notConfigured();
        }

        return $this->asyncClient;
    }
}
