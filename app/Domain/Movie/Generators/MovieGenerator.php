<?php

namespace App\Domain\Movie\Generators;

use App\Support\Random\DeterministicRandom;
use App\Support\Random\SeedCombiner;
use App\Domain\Movie\Services\LocaleRepository;
use Faker\Factory as FakerFactory;

final class MovieGenerator
{
    private const FAKER_SUPPORTED_LOCALES = ['ru_RU', 'en_US'];

    public function __construct(
        private readonly LocaleRepository $locales
    ) {}

    /**
     * @return array{title: string, actors: string[], genre: string, year: int, likes: int, reviews: array}
     */
    public function generate(
        int $pageSeed,
        int $movieIndex,
        string $locale,
        float $likesAvg = 0.0,
        float $reviewsAvg = 0.0
    ): array {
        return [
            'title' => $this->generateTitle($pageSeed, $movieIndex, $locale),
            'actors' => $this->generateActors($pageSeed, $movieIndex, $locale),
            'genre' => $this->generateGenre($pageSeed, $movieIndex, $locale),
            'year' => $this->generateYear($pageSeed, $movieIndex),
            'likes' => $this->generateLikes($pageSeed, $movieIndex, $likesAvg),
            'reviews' => $this->generateReviews($pageSeed, $movieIndex, $locale, $reviewsAvg),
        ];
    }

    private function generateTitle(int $pageSeed, int $movieIndex, string $locale): string
    {
        $subSeed = SeedCombiner::forField($pageSeed, $movieIndex, 'title');
        $rng = new DeterministicRandom($subSeed);

        $templates = $this->locales->titleTemplates($locale);
        $words = $this->locales->titleWords($locale);

        if (empty($templates) || empty($words)) {
            return 'Untitled';
        }

        $template = $rng->pick($templates);
        $placeholderCount = substr_count($template, '%s');
        $args = [];
        for ($i = 0; $i < $placeholderCount; $i++) {
            $args[] = $rng->pick($words);
        }
        $title = vsprintf($template, $args);

        return mb_strtoupper(mb_substr($title, 0, 1)) . mb_substr($title, 1);
    }

    private function generateActors(int $pageSeed, int $movieIndex, string $locale): array
    {
        $subSeed = SeedCombiner::forField($pageSeed, $movieIndex, 'actors');
        $rng = new DeterministicRandom($subSeed);
        $count = $rng->nextInt(2, 4);

        if (in_array($locale, self::FAKER_SUPPORTED_LOCALES)) {
            $faker = FakerFactory::create($locale);
            $faker->seed($subSeed);
            $names = [];
            for ($i = 0; $i < $count; $i++) {
                $names[] = $faker->name();
            }
            return $names;
        }

        $firstNames = $this->locales->firstNames($locale);
        $lastNames = $this->locales->lastNames($locale);
        
        if (empty($firstNames) || empty($lastNames)) {
            return ['Unknown Actor'];
        }

        $names = [];
        for ($i = 0; $i < $count; $i++) {
            $names[] = $rng->pick($firstNames) . ' ' . $rng->pick($lastNames);
        }
        return $names;
    }

    private function generateGenre(int $pageSeed, int $movieIndex, string $locale): string
    {
        $subSeed = SeedCombiner::forField($pageSeed, $movieIndex, 'genre');
        $rng = new DeterministicRandom($subSeed);
        $genres = $this->locales->genres($locale);
        
        if (empty($genres)) {
            return 'Unknown';
        }
        
        return $rng->pick($genres);
    }

    private function generateYear(int $pageSeed, int $movieIndex): int
    {
        $subSeed = SeedCombiner::forField($pageSeed, $movieIndex, 'year');
        $rng = new DeterministicRandom($subSeed);
        return $rng->nextInt(1995, 2026);
    }

    private function generateLikes(int $pageSeed, int $movieIndex, float $likesAvg): int
    {
        if ($likesAvg < 0 || $likesAvg > 10) {
            throw new \InvalidArgumentException("likesAvg must be between 0 and 10");
        }

        $subSeed = SeedCombiner::forField($pageSeed, $movieIndex, 'likes');
        $rng = new DeterministicRandom($subSeed);

        $base = (int) floor($likesAvg);
        $fraction = $likesAvg - $base;

        $likes = $base;
        if ($fraction > 0 && $rng->weightedBool($fraction)) {
            $likes++;
        }

        return $likes;
    }

    private function generateReviews(int $pageSeed, int $movieIndex, string $locale, float $reviewsAvg): array
    {
        if ($reviewsAvg < 0 || $reviewsAvg > 10) {
            throw new \InvalidArgumentException("reviewsAvg must be between 0 and 10");
        }

        $subSeed = SeedCombiner::forField($pageSeed, $movieIndex, 'reviews');
        $rng = new DeterministicRandom($subSeed);

        $base = (int) floor($reviewsAvg);
        $fraction = $reviewsAvg - $base;

        $count = $base;
        if ($fraction > 0 && $rng->weightedBool($fraction)) {
            $count++;
        }

        $reviews = [];
        for ($i = 0; $i < $count; $i++) {
            $reviewSeed = SeedCombiner::forReview($pageSeed, $movieIndex, $i);
            $reviewRng = new DeterministicRandom($reviewSeed);

            $tone = $reviewRng->pick(['positive', 'neutral', 'negative']);
            $phrases = $this->locales->reviewPhrases($locale, $tone);

            $reviews[] = [
                'author' => $this->generateReviewAuthor($reviewRng, $locale),
                'text' => empty($phrases) ? '' : $reviewRng->pick($phrases),
            ];
        }

        return $reviews;
    }

    private function generateReviewAuthor(DeterministicRandom $rng, string $locale): string
    {
        if (in_array($locale, self::FAKER_SUPPORTED_LOCALES)) {
            $faker = FakerFactory::create($locale);
            $faker->seed($rng->nextInt(0, 2147483647)); // 2^31 - 1, безопасный диапазон, без overflow
            return $faker->name();
        }

        $firstNames = $this->locales->firstNames($locale);
        $lastNames = $this->locales->lastNames($locale);

        if (empty($firstNames) || empty($lastNames)) {
            return 'Anonymous';
        }

        return $rng->pick($firstNames) . ' ' . $rng->pick($lastNames);
    }
}