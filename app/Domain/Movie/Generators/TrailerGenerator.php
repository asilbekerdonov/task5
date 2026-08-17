<?php

namespace App\Domain\Movie\Generators;

use App\Support\Random\DeterministicRandom;
use App\Support\Random\SeedCombiner;
use App\Domain\Movie\Services\ClipPoolRepository;

final class TrailerGenerator
{
    private const MIN_CLIPS = 2;
    private const MAX_CLIPS = 3;
    private const TARGET_DURATION_MIN = 5.0;
    private const TARGET_DURATION_MAX = 10.0;

    public function __construct(
        private readonly ClipPoolRepository $clipPool
    ) {}

    /**
     * @return array{
     *   duration: float,
     *   titleAnimation: array{type: string},
     *   taglineAnimation: array{text: string, type: string}|null,
     *   clips: array<array{clipId: string, source: string, zoom: float, speed: float, colorFilter: string}>,
     *   transitions: string[],
     *   freezeFrame: array{clipId: string, source: string, frame: string}
     * }
     */
    public function generate(int $pageSeed, int $movieIndex): array
    {
        $subSeed = SeedCombiner::forField($pageSeed, $movieIndex, 'trailer');
        $rng = new DeterministicRandom($subSeed);

        $pool = $this->clipPool->all();
        $effects = $this->clipPool->effectsConfig();

        if (empty($pool) || empty($effects)) {
            throw new \RuntimeException('Clip pool or effects config is empty — cannot generate trailer');
        }

        $clipCount = $rng->nextInt(self::MIN_CLIPS, self::MAX_CLIPS);
        $selectedClips = $rng->pickMultiple($pool, $clipCount);

        $clips = [];
        foreach ($selectedClips as $clip) {
            $clips[] = [
                'clipId' => $clip['id'],
                'source' => $clip['source'],
                'zoom' => round($rng->nextFloat() * 0.3 + 1.0, 2),
                'speed' => round($rng->nextFloat() * 0.4 + 0.85, 2),
                'colorFilter' => $rng->pick($effects['color_filters']),
            ];
        }

        $transitions = [];
        for ($i = 0; $i < $clipCount - 1; $i++) {
            $transitions[] = $rng->pick($effects['transitions']);
        }

        $titleAnimation = [
            'type' => $rng->pick($effects['title_animations']),
        ];

        $taglineAnimation = null;
        if ($rng->weightedBool(0.6)) {
            $taglineAnimation = [
                'text' => $rng->pick($effects['taglines']),
                'type' => $rng->pick($effects['title_animations']),
            ];
        }

        // Freeze frame — первый выбранный клип (детерминировано, без доп. RNG-вызова)
        $freezeFrameSource = $selectedClips[0];

        $duration = round(
            $rng->nextFloat() * (self::TARGET_DURATION_MAX - self::TARGET_DURATION_MIN) + self::TARGET_DURATION_MIN,
            1
        );

        return [
            'duration' => $duration,
            'titleAnimation' => $titleAnimation,
            'taglineAnimation' => $taglineAnimation,
            'clips' => $clips,
            'transitions' => $transitions,
            'freezeFrame' => [
                'clipId' => $freezeFrameSource['id'],
                'source' => $freezeFrameSource['source'],
                'frame' => $freezeFrameSource['frame'] ?? '',
            ],
        ];
    }
}