<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Danielgnh\PolymarketPhp\Client;

/**
 * Example: Fetching user positions and analytics from Data API.
 *
 * This example demonstrates how to use the Data API to:
 * - Get current (open) positions for a user
 * - Get closed positions (historical)
 * - Calculate total position value
 * - Get trade history
 * - Get on-chain activity
 * - Get market analytics (holders, open interest, volume)
 * - Get leaderboard data
 */

// Initialize client (no authentication needed for Data API)
$client = new Client();

// Example wallet address
$walletAddress = '0x0000000000000000000000000000000000000000';

echo "Data API Examples\n";
echo "=================\n\n";

// 1. Get current (open) positions
try {
    echo "1. Fetching current positions...\n";
    $positions = $client->data()->positions()->get($walletAddress);
    echo 'Found '.count($positions)." open positions\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// 2. Get closed positions
try {
    echo "2. Fetching closed positions...\n";
    $closedPositions = $client->data()->positions()->closed($walletAddress);
    echo 'Found '.count($closedPositions)." closed positions\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// 3. Get total position value
try {
    echo "3. Calculating total position value...\n";
    $value = $client->data()->positions()->value($walletAddress);
    echo "Total value: \${$value['total']}\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// 4. Get trade history
try {
    echo "4. Fetching trade history...\n";
    $trades = $client->data()->trades()->get($walletAddress, ['limit' => 10]);
    echo 'Found '.count($trades)." recent trades\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// 5. Get on-chain activity
try {
    echo "5. Fetching on-chain activity...\n";
    $activity = $client->data()->activity()->get($walletAddress, ['limit' => 10]);
    echo 'Found '.count($activity)." activity records\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// 6. Get market analytics (example condition ID)
$conditionId = '0x0000000000000000000000000000000000000000000000000000000000000000';

try {
    echo "6. Fetching market holders...\n";
    $holders = $client->data()->analytics()->holders($conditionId, ['limit' => 5]);
    echo 'Found '.count($holders)." top holders\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

try {
    echo "7. Fetching open interest...\n";
    $openInterest = $client->data()->analytics()->openInterest($conditionId);
    echo "Open interest data retrieved\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// 8. Get leaderboard
try {
    echo "8. Fetching leaderboard...\n";
    $leaderboard = $client->data()->leaderboards()->get(['limit' => 10]);
    echo 'Found '.count($leaderboard)." top traders\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// 9. Get builder leaderboard
try {
    echo "9. Fetching builder leaderboard...\n";
    $builderLeaderboard = $client->data()->leaderboards()->builder(['limit' => 10]);
    echo 'Found '.count($builderLeaderboard)." builders\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// 10. Check API health
try {
    echo "10. Checking Data API health...\n";
    $health = $client->data()->health()->check();
    echo "Status: {$health['status']}\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
