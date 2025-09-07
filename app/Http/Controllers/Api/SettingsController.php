<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * Get all public settings
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => SettingsService::getSiteConfig(),
        ]);
    }

    /**
     * Get settings by group
     */
    public function byGroup(string $group): JsonResponse
    {
        $settings = SettingsService::getByGroup($group);

        return response()->json([
            'data' => $settings,
        ]);
    }    /**
     * Get a specific setting
     */
    public function show(string $key): JsonResponse
    {
        $value = SettingsService::get($key);

        if ($value === null) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'key' => $key,
                'value' => $value,
            ],
        ]);
    }
}
