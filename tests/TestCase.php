<?php

namespace Tests;

use App\Services\EntityHierarchyService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Static caches in EntityHierarchyService hold entity models across
        // tests, which become stale when RefreshDatabase resets the DB.
        EntityHierarchyService::clearCache();
    }
}
