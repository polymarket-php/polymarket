<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PolymarketPhp\Polymarket\Client;
use PolymarketPhp\Polymarket\Exceptions\PolymarketException;
use GuzzleHttp\Promise\Utils;

$client = new Client();

try {
    // Fetch market list
    $markets = $client->gamma()->markets()->list(['active' => true], limit: 10);

    // Concurrent batch fetching - fetch multiple markets in parallel
    $marketIds = array_column(array_slice($markets, 0, 5), 'id');
    $batchResult = $client->gamma()->markets()->getMany($marketIds);

    foreach ($batchResult as $id => $market) {
        echo "Market: {$market['id']}\n";
    }

    // Handle any failures
    if ($batchResult->hasFailures()) {
        foreach ($batchResult->failed as $id => $exception) {
            echo "Failed to fetch {$id}: {$exception->getMessage()}\n";
        }
    }

    // Promise-based async API for custom workflows
    $promise1 = $client->gamma()->markets()->getAsync($marketIds[0] ?? 'test-id');
    $promise2 = $client->gamma()->markets()->listAsync(['active' => true], limit: 5);
    $promise3 = $client->gamma()->markets()->getBySlugAsync('trump-popular-vote-2024');

    // Wait for promises to resolve
    $results = Utils::unwrap([
        'market1' => $promise1,
        'marketList' => $promise2,
        'bySlug' => $promise3,
    ]);

} catch (PolymarketException $e) {
    echo "Error: {$e->getMessage()}\n";
}
