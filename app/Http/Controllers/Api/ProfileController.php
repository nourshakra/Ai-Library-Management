<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected RecommendationService $recommendations)
    {
    }

    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        // Interests changed -> the profile embedding used for matching must be refreshed.
        $this->recommendations->refreshUserEmbedding($user);

        return response()->json($user);
    }
}
