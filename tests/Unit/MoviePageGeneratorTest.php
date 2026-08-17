<?php

namespace Tests\Unit;

use App\Domain\Movie\Generators\MovieGenerator;
use App\Domain\Movie\Services\LocaleRepository;
use App\Domain\Movie\Services\MoviePageGenerator;
use Tests\TestCase;

class MoviePageGeneratorTest extends TestCase
{
    private MoviePageGenerator $pageGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $movieGenerator = new MovieGenerator(new LocaleRepository());
        $this->pageGenerator = new MoviePageGenerator($movieGenerator);
    }

    public function test_generates_correct_number_of_movies(): void
    {
        $params = [
            'seed' => 42,
            'page' => 1,
            'per_page' => 20,
            'locale' => 'ru_RU',
            'likes' => 3.7,
            'reviews' => 2.5,
        ];

        $result = $this->pageGenerator->generate($params);
        $this->assertCount(20, $result['data']);
    }

    public function test_movie_indices_are_sequential(): void
    {
        $params = [
            'seed' => 42,
            'page' => 1,
            'per_page' => 20,
            'locale' => 'ru_RU',
            'likes' => 0,
            'reviews' => 0,
        ];

        $result1 = $this->pageGenerator->generate($params);
        $this->assertEquals(1, $result1['data'][0]['index']);
        $this->assertEquals(20, $result1['data'][19]['index']);

        $params['page'] = 2;
        $result2 = $this->pageGenerator->generate($params);
        $this->assertEquals(21, $result2['data'][0]['index']);
        $this->assertEquals(40, $result2['data'][19]['index']);
    }

    public function test_same_params_produce_same_page(): void
    {
        $params = [
            'seed' => 42,
            'page' => 1,
            'per_page' => 5,
            'locale' => 'ru_RU',
            'likes' => 3.7,
            'reviews' => 2.5,
        ];

        $result1 = $this->pageGenerator->generate($params);
        $result2 = $this->pageGenerator->generate($params);

        $this->assertEquals($result1, $result2);
    }

    public function test_different_pages_produce_different_movies(): void
    {
        $params = [
            'seed' => 42,
            'page' => 1,
            'per_page' => 5,
            'locale' => 'ru_RU',
            'likes' => 0,
            'reviews' => 0,
        ];

        $result1 = $this->pageGenerator->generate($params);
        
        $params['page'] = 2;
        $result2 = $this->pageGenerator->generate($params);

        $this->assertNotEquals($result1['data'][0]['title'], $result2['data'][0]['title']);
    }

    public function test_meta_contains_correct_page_info(): void
    {
        $params = [
            'seed' => 42,
            'page' => 3,
            'per_page' => 10,
            'locale' => 'en_US',
            'likes' => 0,
            'reviews' => 0,
        ];

        $result = $this->pageGenerator->generate($params);

        $this->assertEquals(3, $result['meta']['page']);
        $this->assertEquals(10, $result['meta']['per_page']);
        $this->assertTrue($result['meta']['has_more']);
    }
}