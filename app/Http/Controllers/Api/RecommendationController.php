<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(protected RecommendationService $recommendations)
    {
    }

    /**
     * Personalized book recommendations for the authenticated user,
     * sorted by match percentage (highest first).
     */
    public function index(Request $request)
    {
        $results = $this->recommendations->recommendationsFor($request->user());

        return response()->json(
            $results->map(fn ($r) => [
                'book' => $r['book'],
                'match' => $r['match'],
            ])
        );
    }
}
