<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Exceptions\NotFoundException;
use Danielgnh\PolymarketPhp\Http\BatchResult;
use Danielgnh\PolymarketPhp\Http\RequestPool;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;

describe('RequestPool::batch', function (): void {
    it('resolves all successful promises', function (): void {
        $pool = new RequestPool();

        $promises = [
            'a' => new FulfilledPromise(['id' => 'a']),
            'b' => new FulfilledPromise(['id' => 'b']),
            'c' => new FulfilledPromise(['id' => 'c']),
        ];

        $result = $pool->batch($promises);

        expect($result)->toBeInstanceOf(BatchResult::class);
        expect($result->allSucceeded())->toBeTrue();
        expect($result->succeeded)->toHaveCount(3);
        expect($result->succeeded['a'])->toBe(['id' => 'a']);
    });

    it('handles partial failures', function (): void {
        $pool = new RequestPool();
        $exception = new NotFoundException('Not found');

        $promises = [
            'a' => new FulfilledPromise(['id' => 'a']),
            'b' => new RejectedPromise($exception),
            'c' => new FulfilledPromise(['id' => 'c']),
        ];

        $result = $pool->batch($promises);

        expect($result->hasFailures())->toBeTrue();
        expect($result->succeeded)->toHaveCount(2);
        expect($result->failed)->toHaveCount(1);
        expect($result->failed['b'])->toBe($exception);
    });

    it('handles all failures', function (): void {
        $pool = new RequestPool();
        $exception = new NotFoundException('Not found');

        $promises = [
            'a' => new RejectedPromise($exception),
            'b' => new RejectedPromise($exception),
        ];

        $result = $pool->batch($promises);

        expect($result->allSucceeded())->toBeFalse();
        expect($result->succeeded)->toBeEmpty();
        expect($result->failed)->toHaveCount(2);
    });

    it('uses custom concurrency', function (): void {
        $pool = new RequestPool(defaultConcurrency: 5);

        $promises = [
            'a' => new FulfilledPromise(['id' => 'a']),
        ];

        $result = $pool->batch($promises, concurrency: 2);

        expect($result->allSucceeded())->toBeTrue();
    });
});

describe('RequestPool::each', function (): void {
    it('calls onFulfilled for each successful result', function (): void {
        $pool = new RequestPool();
        $results = [];

        $promises = [
            'a' => new FulfilledPromise(['id' => 'a']),
            'b' => new FulfilledPromise(['id' => 'b']),
        ];

        $pool->each(
            $promises,
            function ($result, $key) use (&$results): void {
                $results[$key] = $result;
            }
        );

        expect($results)->toHaveCount(2);
        expect($results['a'])->toBe(['id' => 'a']);
    });

    it('calls onRejected for failures', function (): void {
        $pool = new RequestPool();
        $errors = [];
        $exception = new NotFoundException('Not found');

        $promises = [
            'a' => new FulfilledPromise(['id' => 'a']),
            'b' => new RejectedPromise($exception),
        ];

        $pool->each(
            $promises,
            function ($result, $key): void {},
            function ($error, $key) use (&$errors): void {
                $errors[$key] = $error;
            }
        );

        expect($errors)->toHaveCount(1);
        expect($errors['b'])->toBe($exception);
    });
});
