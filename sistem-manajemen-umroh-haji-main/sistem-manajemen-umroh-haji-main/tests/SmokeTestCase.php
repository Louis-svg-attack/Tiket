<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

/**
 * Base class for the smoke test suite. Boots the app, and — per requirement —
 * auto-skips the whole test when the configured database is unreachable, so the
 * suite is safe to run in CI without a database.
 */
abstract class SmokeTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database tidak tersedia — smoke test dilewati: '.$e->getMessage());
        }
    }
}
