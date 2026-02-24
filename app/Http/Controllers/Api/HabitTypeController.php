<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\HabitType;

class HabitTypeController extends Controller
{
    /**
     * Get all habit types (for selection screen in the app)
     */
    public function index()
    {
        $habitTypes = HabitType::orderBy('name')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'description' => $type->description,
                ];
            });

        return ApiResponse::success([
            'habit_types' => $habitTypes,
        ], 'Daftar jenis habit berhasil diambil.');
    }
}
