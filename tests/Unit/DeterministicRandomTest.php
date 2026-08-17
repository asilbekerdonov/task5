<?php

namespace Tests\Unit;

use App\Support\Random\DeterministicRandom;
use Tests\TestCase;

class DeterministicRandomTest extends TestCase
{
    public function test_same_seed_produces_same_sequence(): void
    {
        $rng1 = new DeterministicRandom(12345);
        $rng2 = new DeterministicRandom(12345);
        
        $sequence1 = [];
        $sequence2 = [];
        
        for ($i = 0; $i < 20; $i++) {
            $sequence1[] = $rng1->nextInt(1, 100);
            $sequence2[] = $rng2->nextInt(1, 100);
            $sequence1[] = $rng1->nextFloat();
            $sequence2[] = $rng2->nextFloat();
            $sequence1[] = $rng1->pick([1, 2, 3, 4, 5]);
            $sequence2[] = $rng2->pick([1, 2, 3, 4, 5]);
        }
        
        $this->assertEquals($sequence1, $sequence2);
    }

    public function test_different_seeds_produce_different_sequences(): void
    {
        $rng1 = new DeterministicRandom(11111);
        $rng2 = new DeterministicRandom(22222);
        
        $different = false;
        for ($i = 0; $i < 5; $i++) {
            if ($rng1->nextInt(1, 1000) !== $rng2->nextInt(1, 1000)) {
                $different = true;
                break;
            }
        }
        
        $this->assertTrue($different);
    }

    public function test_weighted_bool_extremes(): void
    {
        $rngFalse = new DeterministicRandom(999);
        for ($i = 0; $i < 1000; $i++) {
            $this->assertFalse($rngFalse->weightedBool(0.0));
        }
        
        $rngTrue = new DeterministicRandom(888);
        for ($i = 0; $i < 1000; $i++) {
            $this->assertTrue($rngTrue->weightedBool(1.0));
        }
    }

    public function test_weighted_bool_statistical_distribution(): void
    {
        $rng = new DeterministicRandom(777);
        $trueCount = 0;
        $total = 10000;
        
        for ($i = 0; $i < $total; $i++) {
            if ($rng->weightedBool(0.5)) {
                $trueCount++;
            }
        }
        
        $ratio = $trueCount / $total;
        $this->assertGreaterThanOrEqual(0.45, $ratio);
        $this->assertLessThanOrEqual(0.55, $ratio);
    }

    public function test_pick_multiple_returns_unique_items(): void
    {
        $rng = new DeterministicRandom(555);
        $items = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'];
        
        $picked = $rng->pickMultiple($items, 5);
        
        $this->assertCount(5, $picked);
        $this->assertCount(5, array_unique($picked));
        $this->assertTrue(count(array_intersect($picked, $items)) === 5);
    }

    public function test_pick_multiple_throws_on_too_many_items(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $rng = new DeterministicRandom(444);
        $rng->pickMultiple([1, 2, 3], 4);
    }
    public function test_next_int_throws_on_overflow_range(): void
    {
        $rng = new DeterministicRandom(123);
        $this->expectException(\InvalidArgumentException::class);
        $rng->nextInt(0, PHP_INT_MAX);
    }
}