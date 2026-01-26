<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Http;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Generator;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Throwable;

class RequestPool
{
    public function __construct(
        private readonly int $defaultConcurrency = 10,
    ) {}

    /**
     * @param array<string, PromiseInterface> $promises
     */
    public function batch(array $promises, ?int $concurrency = null): BatchResult
    {
        /** @var array<string, mixed> $succeeded */
        $succeeded = [];
        /** @var array<string, PolymarketException> $failed */
        $failed = [];

        $this->each(
            $promises,
            function (mixed $value, int|string $key) use (&$succeeded): void {
                $succeeded[$key] = $value;
            },
            function (Throwable $reason, int|string $key) use (&$failed): void {
                $failed[$key] = $reason instanceof PolymarketException
                    ? $reason
                    : new PolymarketException($reason->getMessage(), $reason->getCode(), null, $reason);
            },
            $concurrency
        );

        return new BatchResult($succeeded, $failed);
    }

    /**
     * @param array<string, PromiseInterface> $promises
     */
    public function each(
        array $promises,
        callable $onFulfilled,
        ?callable $onRejected = null,
        ?int $concurrency = null,
    ): void {
        $concurrency ??= $this->defaultConcurrency;

        $eachPromise = new EachPromise($this->yieldPromises($promises), [
            'concurrency' => $concurrency,
            'fulfilled' => function ($value, $key) use ($onFulfilled): void {
                $onFulfilled($value, $key);
            },
            'rejected' => function ($reason, $key) use ($onRejected): void {
                if ($onRejected !== null) {
                    $onRejected($reason, $key);
                }
            },
        ]);

        $eachPromise->promise()->wait();
    }

    /**
     * @param array<string, PromiseInterface> $promises
     * @return Generator<string, PromiseInterface>
     */
    private function yieldPromises(array $promises): Generator
    {
        foreach ($promises as $key => $promise) {
            yield $key => $promise;
        }
    }
}
