<?php

namespace App\Domain\Movie\Services;

final class ClipPoolRepository
{
    private ?array $cache = null;

    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $path = resource_path('trailers/clip_pool.json');
        if (!file_exists($path)) {
            return $this->cache = [];
        }

        return $this->cache = json_decode(file_get_contents($path), true) ?? [];
    }

    public function effectsConfig(): array
    {
        $path = resource_path('trailers/effects_config.json');
        if (!file_exists($path)) {
            return [];
        }
        return json_decode(file_get_contents($path), true) ?? [];
    }
}