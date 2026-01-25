<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Http;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use IteratorAggregate;
use LogicException;
use Traversable;

/**
 * @implements ArrayAccess<string, mixed>
 * @implements IteratorAggregate<string, mixed>
 */
class BatchResult implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param array<string, mixed> $succeeded
     * @param array<string, PolymarketException> $failed
     */
    public function __construct(
        public readonly array $succeeded,
        public readonly array $failed,
    ) {}

    public function hasFailures(): bool
    {
        return count($this->failed) > 0;
    }

    public function allSucceeded(): bool
    {
        return count($this->failed) === 0;
    }

    public function count(): int
    {
        return count($this->succeeded);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->succeeded);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->succeeded[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->succeeded[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('BatchResult is immutable');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('BatchResult is immutable');
    }
}
