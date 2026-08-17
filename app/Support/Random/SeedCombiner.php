<?php

namespace App\Support\Random;

use App\Domain\Movie\ValueObjects\Seed;

final class SeedCombiner
{
    private const A = '6364136223846793005';
    private const M = '281474976710656'; // 2^48

    public static function forPage(Seed $seed, int $page): int
    {
        $product = bcmul((string) $seed->value, self::A);
        $sum = bcadd($product, (string) $page);
        $mod = bcmod($sum, self::M);
        return (int) $mod;
    }

    public static function forField(int $pageSeed, int $movieIndex, string $stream): int
    {
        $raw = $pageSeed . ':' . $movieIndex . ':' . $stream;
        return crc32($raw);
    }

    public static function forReview(int $pageSeed, int $movieIndex, int $reviewIndex): int
    {
        $raw = $pageSeed . ':' . $movieIndex . ':review:' . $reviewIndex;
        return crc32($raw);
    }
}