<?php

namespace Tests\Unit;

use App\Domain\Movie\Generators\TrailerGenerator;
use App\Domain\Movie\Services\ClipPoolRepository;
use Tests\TestCase;

class TrailerGeneratorTest extends TestCase
{
    private TrailerGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new TrailerGenerator(new ClipPoolRepository());
    }

    public function test_generate_is_deterministic(): void
    {
        $pageSeed = 987654321;
        $movieIndex = 1;

        $result1 = $this->generator->generate($pageSeed, $movieIndex);
        $result2 = $this->generator->generate($pageSeed, $movieIndex);

        $this->assertEquals($result1, $result2);
    }

    public function test_different_movie_index_produces_different_trailer(): void
    {
        $pageSeed = 987654321;

        $trailer1 = $this->generator->generate($pageSeed, 1);
        $trailer2 = $this->generator->generate($pageSeed, 2);

        $this->assertNotEquals(
            array_column($trailer1['clips'], 'clipId'),
            array_column($trailer2['clips'], 'clipId')
        );
    }

    public function test_clip_count_within_bounds(): void
    {
        $pageSeed = 42;

        for ($movieIndex = 1; $movieIndex <= 20; $movieIndex++) {
            $trailer = $this->generator->generate($pageSeed, $movieIndex);
            $this->assertGreaterThanOrEqual(2, count($trailer['clips']));
            $this->assertLessThanOrEqual(3, count($trailer['clips']));
        }
    }

    public function test_duration_within_target_range(): void
    {
        $pageSeed = 42;

        for ($movieIndex = 1; $movieIndex <= 20; $movieIndex++) {
            $trailer = $this->generator->generate($pageSeed, $movieIndex);
            $this->assertGreaterThanOrEqual(5.0, $trailer['duration']);
            $this->assertLessThanOrEqual(10.0, $trailer['duration']);
        }
    }

    public function test_zoom_and_speed_within_bounds(): void
    {
        $pageSeed = 42;

        for ($movieIndex = 1; $movieIndex <= 20; $movieIndex++) {
            $trailer = $this->generator->generate($pageSeed, $movieIndex);
            
            foreach ($trailer['clips'] as $clip) {
                $this->assertGreaterThanOrEqual(1.0, $clip['zoom']);
                $this->assertLessThanOrEqual(1.3, $clip['zoom']);
                $this->assertGreaterThanOrEqual(0.85, $clip['speed']);
                $this->assertLessThanOrEqual(1.25, $clip['speed']);
            }
        }
    }

    public function test_transitions_count_matches_clips_minus_one(): void
    {
        $pageSeed = 42;

        for ($movieIndex = 1; $movieIndex <= 20; $movieIndex++) {
            $trailer = $this->generator->generate($pageSeed, $movieIndex);
            $this->assertCount(count($trailer['clips']) - 1, $trailer['transitions']);
        }
    }

    public function test_freeze_frame_references_existing_clip(): void
    {
        $pageSeed = 42;
        $movieIndex = 1;

        $trailer = $this->generator->generate($pageSeed, $movieIndex);
        $clipIds = array_column($trailer['clips'], 'clipId');

        $this->assertContains($trailer['freezeFrame']['clipId'], $clipIds);
    }
}