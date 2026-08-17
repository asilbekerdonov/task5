<?php

namespace Tests\Unit;

use App\Domain\Movie\ValueObjects\Seed;
use PHPUnit\Framework\TestCase;

class SeedTest extends TestCase
{
    public function test_seed_accepts_valid_values(): void
    {
        $seed = new Seed(0);
        $this->assertEquals(0, $seed->value);
        
        $seed = new Seed(281474976710655);
        $this->assertEquals(281474976710655, $seed->value);
    }

    public function test_seed_rejects_negative_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Seed(-1);
    }

    public function test_seed_rejects_values_above_max(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Seed(281474976710656);
    }

    public function test_random_returns_seed_in_range(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $seed = Seed::random();
            $this->assertGreaterThanOrEqual(0, $seed->value);
            $this->assertLessThanOrEqual(281474976710655, $seed->value);
        }
    }
}