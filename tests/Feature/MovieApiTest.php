<?php

namespace Tests\Feature;

use Tests\TestCase;

class MovieApiTest extends TestCase
{
    public function test_movies_endpoint_returns_200(): void
    {
        $response = $this->getJson('/api/movies');
        $response->assertStatus(200);
    }

    public function test_movies_endpoint_has_correct_structure(): void
    {
        $response = $this->getJson('/api/movies?seed=42&locale=ru_RU&likes=3.7&reviews=2.5');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    [
                        'index',
                        'title',
                        'actors',
                        'genre',
                        'year',
                        'likes',
                        'reviews' => [
                            [
                                'author',
                                'text',
                            ],
                        ],
                    ],
                ],
                'meta' => [
                    'page',
                    'per_page',
                    'has_more',
                ],
            ]);
    }

    public function test_default_per_page_is_20(): void
    {
        $response = $this->getJson('/api/movies');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(20, $data);
    }

    public function test_per_page_respects_max_100(): void
    {
        $response = $this->getJson('/api/movies?per_page=500');
        $response->assertStatus(422);
    }

    public function test_invalid_locale_returns_422(): void
    {
        $response = $this->getJson('/api/movies?locale=fr_FR');
        $response->assertStatus(422);
    }

    public function test_seed_out_of_range_returns_422(): void
    {
        $response = $this->getJson('/api/movies?seed=-1');
        $response->assertStatus(422);

        $response = $this->getJson('/api/movies?seed=999999999999999999');
        $response->assertStatus(422);
    }

    public function test_same_seed_returns_same_data(): void
    {
        $response1 = $this->getJson('/api/movies?seed=123&page=1');
        $response2 = $this->getJson('/api/movies?seed=123&page=1');
        
        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_different_page_returns_different_data(): void
    {
        $response1 = $this->getJson('/api/movies?seed=123&page=1');
        $response2 = $this->getJson('/api/movies?seed=123&page=2');
        
        $this->assertNotEquals(
            $response1->json('data.0.title'),
            $response2->json('data.0.title')
        );
    }

    public function test_changing_likes_does_not_change_title(): void
    {
        $response1 = $this->getJson('/api/movies?seed=123&page=1&likes=1');
        $response2 = $this->getJson('/api/movies?seed=123&page=1&likes=9');
        
        $this->assertEquals(
            $response1->json('data.0.title'),
            $response2->json('data.0.title')
        );
        
        $this->assertNotEquals(
            $response1->json('data.0.likes'),
            $response2->json('data.0.likes')
        );
    }
}