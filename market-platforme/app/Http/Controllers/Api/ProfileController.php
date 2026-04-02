<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
      public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['preference', 'subscriptions.plan']);

        return response()->json([
            'user' => $user,
            'current_subscription' => $user->subscriptions()->latest()->with('plan')->first(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $user,
        ]);
    }

    public function preferences(Request $request): JsonResponse
    {
        $preference = $request->user()->preference;

        return response()->json([
            'preferences' => $preference,
        ]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'favorite_sectors' => ['nullable', 'array'],
            'favorite_geographies' => ['nullable', 'array'],
            'favorite_periods' => ['nullable', 'array'],
        ]);

        $preference = UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json([
            'message' => 'Préférences mises à jour.',
            'preferences' => $preference,
        ]);
    }

    public function searchHistory(Request $request): JsonResponse
    {
        return response()->json([
            'searches' => $request->user()->searchHistories()->latest()->get(),
        ]);
    }

    public function viewHistory(Request $request): JsonResponse
    {
        return response()->json([
            'views' => $request->user()->reportViews()->with('report')->latest()->get(),
        ]);
    }

    public function downloadHistory(Request $request): JsonResponse
    {
        return response()->json([
            'downloads' => $request->user()->downloadHistories()->with('report')->latest()->get(),
        ]);
    }
}
