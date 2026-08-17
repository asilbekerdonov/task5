<?php

namespace App\Domain\Movie\Services;

use App\Domain\Movie\Generators\MovieGenerator;
use App\Domain\Movie\ValueObjects\Seed;
use App\Support\Random\SeedCombiner;

final class MoviePageGenerator
{
    public function __construct(
        private readonly MovieGenerator $movieGenerator
    ) {}

    /**
     * @return array{data: array, meta: array}
     */
    public function generate(array $params): array
    {
        $seed = new Seed($params['seed']);
        $pageSeed = SeedCombiner::forPage($seed, $params['page']);

        $startIndex = ($params['page'] - 1) * $params['per_page'] + 1;
        $endIndex = $startIndex + $params['per_page'] - 1;

        $movies = [];
        for ($movieIndex = $startIndex; $movieIndex <= $endIndex; $movieIndex++) {
            $movie = $this->movieGenerator->generate(
                $pageSeed,
                $movieIndex,
                $params['locale'],
                $params['likes'],
                $params['reviews']
            );
            $movies[] = array_merge(['index' => $movieIndex], $movie);
        }

        return [
            'data' => $movies,
            'meta' => [
                'page' => $params['page'],
                'per_page' => $params['per_page'],
                'has_more' => true,
            ],
        ];
    }
}