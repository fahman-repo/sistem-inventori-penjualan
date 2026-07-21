<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Disable CSRF middleware for all tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF token validation in testing environment
        $this->withoutMiddleware(
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
        );
    }
}
