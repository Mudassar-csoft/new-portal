<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\JsonResponse;

class CampusController extends Controller
{
    public function show(string $id): JsonResponse
    {
        $campus = Campus::query()
            ->where('status', 'active')
            ->find($id);

        if ($campus === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Campus not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $campus->id,
                'name' => $campus->name,
                'title' => $campus->title ?? $campus->name,
                'slug' => $campus->slug,
                'code' => $campus->code,
                'country' => $campus->country,
                'city' => $campus->city,
                'city_abbr' => $campus->city_abbr,
                'campus_type' => $campus->campus_type,
                'campus_email' => $campus->campus_email,
                'landline' => $campus->landline,
                'mobile' => $campus->mobile,
                'address' => $campus->address,
                'labs_count' => (int) $campus->labs_count,
                'status' => $campus->status,
            ],
        ]);
    }
}
