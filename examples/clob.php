<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PolymarketPhp\Polymarket\Client;
use PolymarketPhp\Polymarket\Exceptions\PolymarketException;
use GuzzleHttp\Promise\Utils;

$client = new Client();

try {
     $client->auth();

     // Fetch single order
     $order = $client
	     ->clob()
	     ->markets()
	     ->get('60487116984468020978247225474488676749601001829886755968952521846780452448915');

	 // Get orders list
	$ordersList = $client
		->clob()
		->orders()
		->list(limit: 20);

	// Concurrent batch fetching - fetch multiple orders in parallel
	$orderIds = array_slice(array_column($ordersList['data'] ?? [], 'id'), 0, 10);

	if ($orderIds !== []) {
		$batchResult = $client->clob()->orders()->getMany($orderIds);

		foreach ($batchResult as $id => $orderData) {
			echo "Order {$id}: Status = {$orderData['status']}\n";
		}

		if ($batchResult->hasFailures()) {
			echo "\n=== Failed Order Fetches ===\n";
			foreach ($batchResult->failed as $id => $exception) {
				echo "Failed to fetch order {$id}:{$exception->getMessage()}\n";
			}
		}
	}

	// Batch cancel multiple orders. Lower concurrency used for write operations
	$openOrders = $client->clob()->orders()->getOpen();
	$openOrderIds = array_slice(array_column($openOrders['data'] ?? [], 'id'), 0, 5);

	if ($openOrderIds !== []) {
		$cancelResult = $client->clob()->orders()->cancelMany($openOrderIds, concurrency: 5);

		if ($cancelResult->hasFailures()) {
			foreach ($cancelResult->failed as $id => $exception) {
				echo "Failed to cancel {$id}: {$exception->getMessage()}\n";
			}
		}
	}

	// Promise-based async API for custom workflows
	if ($orderIds !== []) {
		$promises = [
			'order1' => $client->clob()->orders()->getAsync($orderIds[0]),
			'order2' => $client->clob()->orders()->getAsync($orderIds[1] ?? $orderIds[0]),
			'openOrders' => $client->clob()->orders()->getOpenAsync(),
		];
		$results = Utils::unwrap($promises);
		echo "Fetched order: {$results['order1']['id']}\n";
		echo "Fetched " . count($results['openOrders']['data'] ?? []) . " open orders\n";
	}



} catch (PolymarketException $e) {
	echo "Error: {$e->getMessage()}\n";
}
