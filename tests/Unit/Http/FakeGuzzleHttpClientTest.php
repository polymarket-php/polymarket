<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Exceptions\NotFoundException;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;
use Danielgnh\PolymarketPhp\Http\Response;

describe('FakeGuzzleHttpClient sync methods', function (): void {
    it('get returns mocked response', function (): void {
        $fake = new FakeGuzzleHttpClient();
        $fake->addJsonResponse('GET', '/test', ['id' => '123']);

        $response = $fake->get('/test');

        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toBe(['id' => '123']);
    });

    it('post returns mocked response', function (): void {
        $fake = new FakeGuzzleHttpClient();
        $fake->addJsonResponse('POST', '/test', ['created' => true]);

        $response = $fake->post('/test', ['data' => 'value']);

        expect($response->json())->toBe(['created' => true]);
    });

    it('delete returns mocked response', function (): void {
        $fake = new FakeGuzzleHttpClient();
        $fake->addJsonResponse('DELETE', '/test', ['deleted' => true]);

        $response = $fake->delete('/test');

        expect($response->json())->toBe(['deleted' => true]);
    });

    it('returns 404 response for missing mocks', function (): void {
        $fake = new FakeGuzzleHttpClient();

        $response = $fake->get('/unknown');

        expect($response->statusCode())->toBe(404);
    });

    it('tracks requests via hasRequest', function (): void {
        $fake = new FakeGuzzleHttpClient();
        $fake->addJsonResponse('GET', '/test', ['id' => '123']);

        $fake->get('/test');

        expect($fake->hasRequest('GET', '/test'))->toBeTrue();
        expect($fake->hasRequest('POST', '/test'))->toBeFalse();
    });

    it('addExceptionResponse causes exception on sync call', function (): void {
        $fake = new FakeGuzzleHttpClient();
        $fake->addExceptionResponse('GET', '/error', new NotFoundException('Not found'));

        expect(fn (): Response => $fake->get('/error'))->toThrow(NotFoundException::class);
    });
});
