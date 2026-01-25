<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Exceptions\NotFoundException;
use Danielgnh\PolymarketPhp\Http\BatchResult;

describe('BatchResult', function (): void {
    it('stores succeeded and failed results', function (): void {
        $succeeded = ['id1' => ['data' => 'value1'], 'id2' => ['data' => 'value2']];
        $failed = ['id3' => new NotFoundException('Not found')];

        $result = new BatchResult($succeeded, $failed);

        expect($result->succeeded)->toBe($succeeded)
            ->and($result->failed)->toHaveKey('id3')
            ->and($result->failed['id3'])->toBeInstanceOf(NotFoundException::class);
    });

    it('reports hasFailures correctly', function (): void {
        $withFailures = new BatchResult(['id1' => []], ['id2' => new NotFoundException('x')]);
        $withoutFailures = new BatchResult(['id1' => []], []);

        expect($withFailures->hasFailures())->toBeTrue();
        expect($withoutFailures->hasFailures())->toBeFalse();
    });

    it('reports allSucceeded correctly', function (): void {
        $allSuccess = new BatchResult(['id1' => [], 'id2' => []], []);
        $partial = new BatchResult(['id1' => []], ['id2' => new NotFoundException('x')]);

        expect($allSuccess->allSucceeded())->toBeTrue();
        expect($partial->allSucceeded())->toBeFalse();
    });

    it('is countable and counts succeeded items', function (): void {
        $result = new BatchResult(['id1' => [], 'id2' => [], 'id3' => []], ['id4' => new NotFoundException('x')]);

        expect(count($result))->toBe(3);
    });

    it('is iterable over succeeded items', function (): void {
        $succeeded = ['id1' => ['a' => 1], 'id2' => ['a' => 2]];
        $result = new BatchResult($succeeded, []);

        $items = [];
        foreach ($result as $key => $value) {
            $items[$key] = $value;
        }

        expect($items)->toBe($succeeded);
    });

    it('supports array access for succeeded items', function (): void {
        $result = new BatchResult(['id1' => ['data' => 'test']], []);

        expect(isset($result['id1']))->toBeTrue();
        expect(isset($result['id2']))->toBeFalse();
        expect($result['id1'])->toBe(['data' => 'test']);
    });

    it('throws on array set/unset operations', function (): void {
        $result = new BatchResult([], []);

        expect(function () use ($result): void {
            $result['id1'] = [];
        })->toThrow(LogicException::class);
        expect(function () use ($result): void {
            unset($result['id1']);
        })->toThrow(LogicException::class);
    });
});
