<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Enums\OrderSide;
use Danielgnh\PolymarketPhp\Enums\RfqSortBy;
use Danielgnh\PolymarketPhp\Enums\RfqSortDir;
use Danielgnh\PolymarketPhp\Enums\RfqState;

/**
 * Example: RFQ (Request for Quote) Workflow.
 *
 * This example demonstrates the complete RFQ workflow for institutional trading:
 * 1. Requester creates an RFQ request
 * 2. Market maker creates a quote in response
 * 3. Requester accepts the quote
 * 4. Market maker approves the order (last look)
 *
 * RFQ is designed for large orders and negotiated pricing.
 */

// Initialize client with authentication
$client = new Client();

// Setup authentication (required for RFQ operations)
try {
    $client->auth(); // Uses private key from .env
} catch (Exception $e) {
    echo "Authentication required for RFQ operations\n";
    echo "Set POLYMARKET_PRIVATE_KEY in your .env file\n";
    exit(1);
}

echo "RFQ Workflow Examples\n";
echo "====================\n\n";

// Example token ID
$tokenId = '21742633143463906290569050155826241533067272736897614950488156847949938836455';

// 1. Create an RFQ Request (Requester wants to buy/sell)
echo "1. Creating RFQ Request...\n";
try {
    $request = $client->clob()->rfq()->createRequest([
        'token_id' => $tokenId,
        'side' => OrderSide::BUY->value,
        'size' => '100',
        'price' => '0.55',
        'expiry' => time() + 3600, // Expires in 1 hour
    ]);

    $requestId = $request['id'];
    echo "Created RFQ request: $requestId\n";
    echo "Token: $tokenId\n";
    echo "Side: BUY\n";
    echo "Size: 100 shares\n";
    echo "Target Price: \$0.55\n\n";
} catch (Exception $e) {
    echo "Error creating request: {$e->getMessage()}\n\n";
    $requestId = null;
}

// 2. List all RFQ Requests
echo "2. Listing Active RFQ Requests...\n";
try {
    $requests = $client->clob()->rfq()->listRequests([
        'state' => RfqState::ACTIVE->value,
        'limit' => 10,
        'sort_by' => RfqSortBy::CREATED->value,
        'sort_dir' => RfqSortDir::DESC->value,
    ]);

    echo 'Found '.count($requests)." active RFQ requests\n\n";
} catch (Exception $e) {
    echo "Error listing requests: {$e->getMessage()}\n\n";
}

// 3. Create a Quote (Market Maker responds to request)
if ($requestId !== null) {
    echo "3. Creating Quote for Request...\n";
    try {
        $quote = $client->clob()->rfq()->createQuote([
            'request_id' => $requestId,
            'price' => '0.54',
            'size' => '100',
            'expiry' => time() + 1800, // 30 minutes
        ]);

        $quoteId = $quote['id'];
        echo "Created quote: $quoteId\n";
        echo "Offered Price: \$0.54\n";
        echo "Size: 100 shares\n\n";
    } catch (Exception $e) {
        echo "Error creating quote: {$e->getMessage()}\n\n";
        $quoteId = null;
    }
} else {
    $quoteId = null;
}

// 4. List Quotes for a Request
if ($requestId !== null) {
    echo "4. Listing Quotes for Request...\n";
    try {
        $quotes = $client->clob()->rfq()->listQuotes([
            'request_id' => $requestId,
            'sort_by' => RfqSortBy::PRICE->value,
        ]);

        echo 'Found '.count($quotes)." quotes\n\n";
    } catch (Exception $e) {
        echo "Error listing quotes: {$e->getMessage()}\n\n";
    }
}

// 5. Accept a Quote (Requester accepts market maker's quote)
if ($quoteId !== null) {
    echo "5. Accepting Quote...\n";
    try {
        $acceptance = $client->clob()->rfq()->acceptQuote([
            'quote_id' => $quoteId,
        ]);

        echo "Quote accepted\n";
        echo "Orders created\n\n";
    } catch (Exception $e) {
        echo "Error accepting quote: {$e->getMessage()}\n\n";
    }
}

// 6. Approve Order (Market Maker's last look)
echo "6. Approving Order (Last Look)...\n";
try {
    $approval = $client->clob()->rfq()->approveOrder([
        'order_id' => 'example-order-id',
    ]);

    echo "Order approved\n";
    echo "Trade will execute\n\n";
} catch (Exception $e) {
    echo "Error approving order: {$e->getMessage()}\n\n";
}

// 7. Cancel a Quote (if needed)
if ($quoteId !== null) {
    echo "7. Canceling Quote (Demo)...\n";
    try {
        $cancellation = $client->clob()->rfq()->cancelQuote($quoteId);
        echo "Quote canceled: $quoteId\n\n";
    } catch (Exception $e) {
        echo "Error canceling quote: {$e->getMessage()}\n\n";
    }
}

// 8. Cancel a Request (if needed)
if ($requestId !== null) {
    echo "8. Canceling Request (Demo)...\n";
    try {
        $cancellation = $client->clob()->rfq()->cancelRequest($requestId);
        echo "Request canceled: $requestId\n";
    } catch (Exception $e) {
        echo "Error canceling request: {$e->getMessage()}\n";
    }
}

echo "\nRFQ Workflow Complete!\n";
echo "\nNote: This example demonstrates the full workflow.\n";
echo "In production, different parties (requesters and quoters) would perform different steps.\n";
