<?php

use Yannelli\LaravelPlaud\Exceptions\PlaudException;

describe('PlaudException', function () {
    it('can be instantiated with a message', function () {
        $exception = new PlaudException('Test error message');

        expect($exception)
            ->toBeInstanceOf(PlaudException::class)
            ->and($exception->getMessage())->toBe('Test error message')
            ->and($exception->getCode())->toBe(0);
    });

    it('can be instantiated with a message and code', function () {
        $exception = new PlaudException('Authentication failed', 401);

        expect($exception)
            ->toBeInstanceOf(PlaudException::class)
            ->and($exception->getMessage())->toBe('Authentication failed')
            ->and($exception->getCode())->toBe(401);
    });

    it('can be instantiated with a previous exception', function () {
        $previous = new Exception('Previous error');
        $exception = new PlaudException('Wrapper error', 500, $previous);

        expect($exception->getPrevious())
            ->toBeInstanceOf(Exception::class)
            ->and($exception->getPrevious()->getMessage())->toBe('Previous error');
    });

    it('extends the base Exception class', function () {
        $exception = new PlaudException('Test');

        expect($exception)->toBeInstanceOf(Exception::class);
    });

    it('can be thrown and caught', function () {
        $caught = false;

        try {
            throw new PlaudException('Test exception', 404);
        } catch (PlaudException $e) {
            $caught = true;
            expect($e->getMessage())->toBe('Test exception')
                ->and($e->getCode())->toBe(404);
        }

        expect($caught)->toBeTrue();
    });
});
