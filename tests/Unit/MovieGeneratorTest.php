<?php

namespace Tests\Unit;

use App\Domain\Movie\Generators\MovieGenerator;
use App\Domain\Movie\Services\LocaleRepository;
use Tests\TestCase;

class MovieGeneratorTest extends TestCase
{
    private MovieGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new MovieGenerator(new LocaleRepository());
    }

    public function test_generate_is_deterministic(): void
    {
        $pageSeed = 987654321;
        $movieIndex = 1;
        $locale = 'ru_RU';

        $result1 = $this->generator->generate($pageSeed, $movieIndex, $locale, 3.7, 2.5);
        $result2 = $this->generator->generate($pageSeed, $movieIndex, $locale, 3.7, 2.5);

        $this->assertEquals($result1, $result2);
    }

    public function test_different_movie_index_produces_different_title(): void
    {
        $pageSeed = 987654321;
        $locale = 'ru_RU';

        $movie1 = $this->generator->generate($pageSeed, 1, $locale);
        $movie2 = $this->generator->generate($pageSeed, 2, $locale);

        $this->assertNotEquals($movie1['title'], $movie2['title']);
    }

    public function test_different_locale_produces_different_title(): void
    {
        $pageSeed = 987654321;
        $movieIndex = 1;

        $movieRu = $this->generator->generate($pageSeed, $movieIndex, 'ru_RU');
        $movieEn = $this->generator->generate($pageSeed, $movieIndex, 'en_US');

        $this->assertNotEquals($movieRu['title'], $movieEn['title']);
    }

    public function test_uzbek_locale_generates_valid_data(): void
    {
        $pageSeed = 123456789;
        $movieIndex = 1;
        $locale = 'uz_UZ';

        $movie = $this->generator->generate($pageSeed, $movieIndex, $locale);

        $this->assertNotEmpty($movie['title']);
        $this->assertGreaterThanOrEqual(2, count($movie['actors']));
        $this->assertLessThanOrEqual(4, count($movie['actors']));
        $this->assertNotEmpty($movie['genre']);
        $this->assertGreaterThanOrEqual(1995, $movie['year']);
        $this->assertLessThanOrEqual(2026, $movie['year']);
    }

    public function test_russian_genre_is_from_valid_list(): void
    {
        $pageSeed = 987654321;
        $movieIndex = 1;
        $locale = 'ru_RU';

        $movie = $this->generator->generate($pageSeed, $movieIndex, $locale);
        $validGenres = [
            "Драма", "Комедия", "Боевик", "Триллер", "Ужасы", 
            "Фантастика", "Мелодрама", "Приключения", "Детектив", "Семейный"
        ];

        $this->assertContains($movie['genre'], $validGenres);
    }

    public function test_year_is_in_valid_range(): void
    {
        $pageSeed = 555555;
        
        for ($movieIndex = 1; $movieIndex <= 10; $movieIndex++) {
            $movie = $this->generator->generate($pageSeed, $movieIndex, 'en_US');
            $this->assertGreaterThanOrEqual(1995, $movie['year']);
            $this->assertLessThanOrEqual(2026, $movie['year']);
        }
    }

    public function test_title_does_not_contain_corporate_jargon(): void
    {
        $pageSeed = 42;
        $movie = $this->generator->generate($pageSeed, 1, 'ru_RU');
        $this->assertIsString($movie['title']);
        $this->assertNotEmpty($movie['title']);
        
        $corporateWords = ['synergy', 'paradigm', 'solution', 'infrastructure', 'implementation'];
        foreach ($corporateWords as $word) {
            $this->assertStringNotContainsStringIgnoringCase($word, $movie['title']);
        }
    }

    public function test_likes_respects_average(): void
    {
        $pageSeed = 42;
        
        // likesAvg = 0 → все likes = 0
        for ($movieIndex = 1; $movieIndex <= 50; $movieIndex++) {
            $movie = $this->generator->generate($pageSeed, $movieIndex, 'ru_RU', 0.0);
            $this->assertEquals(0, $movie['likes']);
        }
        
        // likesAvg = 10 → все likes = 10
        for ($movieIndex = 1; $movieIndex <= 50; $movieIndex++) {
            $movie = $this->generator->generate($pageSeed, $movieIndex, 'ru_RU', 10.0);
            $this->assertEquals(10, $movie['likes']);
        }
    }

    public function test_likes_fractional_average_converges(): void
    {
        $pageSeed = 42;
        $totalLikes = 0;
        $count = 1000;
        
        for ($movieIndex = 1; $movieIndex <= $count; $movieIndex++) {
            $movie = $this->generator->generate($pageSeed, $movieIndex, 'ru_RU', 0.5);
            $totalLikes += $movie['likes'];
        }
        
        $average = $totalLikes / $count;
        $this->assertGreaterThanOrEqual(0.4, $average);
        $this->assertLessThanOrEqual(0.6, $average);
    }

    public function test_reviews_respect_average(): void
    {
        $pageSeed = 42;
        
        // reviewsAvg = 0 → 0 отзывов
        for ($movieIndex = 1; $movieIndex <= 10; $movieIndex++) {
            $movie = $this->generator->generate($pageSeed, $movieIndex, 'ru_RU', 0, 0.0);
            $this->assertCount(0, $movie['reviews']);
        }
        
        // reviewsAvg = 3 → ровно 3 отзыва
        for ($movieIndex = 1; $movieIndex <= 10; $movieIndex++) {
            $movie = $this->generator->generate($pageSeed, $movieIndex, 'ru_RU', 0, 3.0);
            $this->assertCount(3, $movie['reviews']);
        }
    }

    public function test_reviews_have_tone(): void
    {
        $pageSeed = 42;
        $movie = $this->generator->generate($pageSeed, 1, 'ru_RU', 0, 3.0);
        
        foreach ($movie['reviews'] as $review) {
            $this->assertNotEmpty($review['text']);
            $this->assertNotEmpty($review['author']);
        }
    }

    public function test_likes_reviews_independent_from_identity(): void
    {
        $pageSeed = 987654321;
        $movieIndex = 1;
        $locale = 'ru_RU';
        
        // Генерируем с низкими likes/reviews
        $movieLow = $this->generator->generate($pageSeed, $movieIndex, $locale, 1.0, 1.0);
        
        // Генерируем с высокими likes/reviews
        $movieHigh = $this->generator->generate($pageSeed, $movieIndex, $locale, 9.0, 9.0);
        
        // Identity поля должны быть идентичны
        $this->assertEquals($movieLow['title'], $movieHigh['title']);
        $this->assertEquals($movieLow['actors'], $movieHigh['actors']);
        $this->assertEquals($movieLow['genre'], $movieHigh['genre']);
        $this->assertEquals($movieLow['year'], $movieHigh['year']);
        
        // А вот likes/reviews должны отличаться
        $this->assertNotEquals($movieLow['likes'], $movieHigh['likes']);
        $this->assertNotEquals($movieLow['reviews'], $movieHigh['reviews']);
    }

    public function test_reviews_are_deterministic(): void
    {
        $pageSeed = 987654321;
        $movieIndex = 1;
        $locale = 'ru_RU';
        
        $movie1 = $this->generator->generate($pageSeed, $movieIndex, $locale, 3.7, 2.5);
        $movie2 = $this->generator->generate($pageSeed, $movieIndex, $locale, 3.7, 2.5);
        
        $this->assertEquals($movie1['reviews'], $movie2['reviews']);
    }
}