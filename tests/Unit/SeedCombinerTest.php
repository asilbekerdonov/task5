<?php

namespace Tests\Unit;

use App\Domain\Movie\ValueObjects\Seed;
use App\Support\Random\SeedCombiner;
use PHPUnit\Framework\TestCase;

class SeedCombinerTest extends TestCase
{
    public function test_different_pages_produce_different_seeds(): void
    {
        $seed = new Seed(123456789);
        
        for ($page = 1; $page < 20; $page++) {
            $pageSeed1 = SeedCombiner::forPage($seed, $page);
            $pageSeed2 = SeedCombiner::forPage($seed, $page + 1);
            
            $this->assertNotEquals(
                $pageSeed1, 
                $pageSeed2, 
                "Pages {$page} and " . ($page + 1) . " should produce different seeds"
            );
        }
    }

    public function test_same_page_produces_same_seed(): void
    {
        $seed = new Seed(123456789);
        
        $results = [];
        for ($i = 0; $i < 5; $i++) {
            $results[] = SeedCombiner::forPage($seed, 1);
        }
        
        $this->assertCount(1, array_unique($results));
    }

    public function test_different_streams_produce_different_field_seeds(): void
    {
        $pageSeed = 987654321;
        
        $titleSeed = SeedCombiner::forField($pageSeed, 1, "title");
        $actorsSeed = SeedCombiner::forField($pageSeed, 1, "actors");
        
        $this->assertNotEquals($titleSeed, $actorsSeed);
    }

    public function test_different_movies_produce_different_field_seeds(): void
    {
        $pageSeed = 987654321;
        
        $movie1Seed = SeedCombiner::forField($pageSeed, 1, "title");
        $movie2Seed = SeedCombiner::forField($pageSeed, 2, "title");
        
        $this->assertNotEquals($movie1Seed, $movie2Seed);
    }

    public function test_review_seeds_are_consistent(): void
    {
        $pageSeed = 555555;
        
        $review1 = SeedCombiner::forReview($pageSeed, 1, 0);
        $review2 = SeedCombiner::forReview($pageSeed, 1, 0);
        $review3 = SeedCombiner::forReview($pageSeed, 1, 1);
        
        $this->assertEquals($review1, $review2);
        $this->assertNotEquals($review1, $review3);
    }

    public function test_no_float_conversion_for_large_seeds(): void
    {
        // Тест с максимальным значением seed
        $seed = new Seed(281474976710655); // 2^48 - 1
        
        $pageSeed1 = SeedCombiner::forPage($seed, 1);
        $pageSeed2 = SeedCombiner::forPage($seed, 2);
        
        $this->assertNotEquals($pageSeed1, $pageSeed2);
        $this->assertIsInt($pageSeed1);
        $this->assertIsInt($pageSeed2);
    }
}