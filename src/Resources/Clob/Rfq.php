<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Clob;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

/**
 * RFQ (Request for Quote) Resource.
 *
 * Handles institutional/large order workflow for negotiated pricing.
 * Enables users to request quotes from market makers and vice versa.
 */
class Rfq extends Resource
{
    /**
     * Create an RFQ request for buying/selling outcome tokens.
     *
     * @param array<string, mixed> $requestData RFQ request data (tokenId, side, size, price, etc.)
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function createRequest(array $requestData): array
    {
        $response = $this->httpClient->post('/rfq/requests', $requestData);

        return $response->json();
    }

    /**
     * Cancel an RFQ request.
     *
     * @param string $requestId RFQ request ID
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function cancelRequest(string $requestId): array
    {
        $response = $this->httpClient->delete("/rfq/requests/$requestId");

        return $response->json();
    }

    /**
     * List RFQ requests with pagination and filters.
     *
     * @param array<string, mixed> $filters Filters (state, tokenId, limit, offset, sortBy, sortDir, etc.)
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function listRequests(array $filters = []): array
    {
        $response = $this->httpClient->get('/rfq/requests', $filters);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Create a quote responding to an RFQ request.
     *
     * @param array<string, mixed> $quoteData Quote data (requestId, price, size, expiry, etc.)
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function createQuote(array $quoteData): array
    {
        $response = $this->httpClient->post('/rfq/quotes', $quoteData);

        return $response->json();
    }

    /**
     * Cancel an RFQ quote.
     *
     * @param string $quoteId RFQ quote ID
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function cancelQuote(string $quoteId): array
    {
        $response = $this->httpClient->delete("/rfq/quotes/$quoteId");

        return $response->json();
    }

    /**
     * List RFQ quotes with pagination and filters.
     *
     * @param array<string, mixed> $filters Filters (state, requestId, limit, offset, sortBy, sortDir, etc.)
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function listQuotes(array $filters = []): array
    {
        $response = $this->httpClient->get('/rfq/quotes', $filters);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Accept a quote and create orders (requester action).
     *
     * @param array<string, mixed> $acceptData Acceptance data (quoteId, etc.)
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function acceptQuote(array $acceptData): array
    {
        $response = $this->httpClient->post('/rfq/accept', $acceptData);

        return $response->json();
    }

    /**
     * Approve an order during last look window (quoter action).
     *
     * @param array<string, mixed> $approveData Approval data (orderId, etc.)
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function approveOrder(array $approveData): array
    {
        $response = $this->httpClient->post('/rfq/approve', $approveData);

        return $response->json();
    }
}
