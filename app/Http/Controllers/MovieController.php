<?php

namespace App\Http\Controllers;

use App\Domain\Movie\Services\MoviePageGenerator;
use App\Http\Requests\MovieIndexRequest;
use Illuminate\Http\JsonResponse;

class MovieController extends Controller
{
    public function __construct(
        private readonly MoviePageGenerator $pageGenerator
    ) {}

    public function index(MovieIndexRequest $request): JsonResponse
    {
        $result = $this->pageGenerator->generate($request->toParameters());
        return response()->json($result);
    }
}