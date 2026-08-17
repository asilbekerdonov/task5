<?php

namespace App\Domain\Movie\Services;

final class LocaleRepository
{
    private array $cache = [];

    public function genres(string $locale): array
    {
        return $this->loadJson($locale, 'genres');
    }

    public function firstNames(string $locale): array
    {
        return $this->loadJson($locale, 'first_names');
    }

    public function lastNames(string $locale): array
    {
        return $this->loadJson($locale, 'last_names');
    }

    public function titleTemplates(string $locale): array
    {
        return $this->loadJson($locale, 'title_templates');
    }

    public function titleWords(string $locale): array
    {
        return $this->loadJson($locale, 'title_words');
    }

    public function reviewPhrases(string $locale, string $tone): array
    {
        return $this->loadJson($locale, "review_phrases_{$tone}");
    }

    private function loadJson(string $locale, string $file): array
    {
        $key = "{$locale}:{$file}";
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $path = resource_path("locales/{$locale}/{$file}.json");
        if (!file_exists($path)) {
            return $this->cache[$key] = [];
        }

        $data = json_decode(file_get_contents($path), true) ?? [];
        return $this->cache[$key] = $data;
    }
}