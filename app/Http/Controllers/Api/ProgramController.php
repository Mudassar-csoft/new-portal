<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramCampusDiscount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProgramController extends Controller
{
    public function index(): JsonResponse
    {
        $programs = Program::query()
            ->where('status', 'active')
            ->with(['campusDiscounts' => function (HasMany $query): void {
                $query->where('status', 'active')
                    ->where(function (Builder $query): void {
                        $query->whereNull('campus_id')
                            ->orWhereHas('campus', fn (Builder $campus) => $campus->where('status', 'active'));
                    })
                    ->with('campus:id,name,code')
                    ->orderBy('id');
            }])
            ->orderByRaw('COALESCE(title, name)')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $programs->map(fn (Program $program): array => [
                'id' => $program->id,
                'name' => $program->name,
                'title' => $program->title ?? $program->name,
                'code' => $program->code,
                'description' => $program->description,
                'program_type' => $program->program_type,
                'fee' => $program->fee === null ? null : number_format((float) $program->fee, 2, '.', ''),
                'duration_weeks' => $program->duration_weeks === null ? null : (int) $program->duration_weeks,
                'installments' => (int) $program->installments,
                'prerequisite' => $program->prerequisite,
                'outline_url' => $program->outline_path ? Storage::disk('public')->url($program->outline_path) : null,
                'status' => $program->status,
                'campus_discounts' => $program->campusDiscounts->map(fn (ProgramCampusDiscount $discount): array => [
                    'campus_id' => $discount->campus_id,
                    'campus_name' => $discount->campus?->name,
                    'campus_code' => $discount->campus?->code,
                    'discount_percent' => number_format((float) $discount->discount_percent, 2, '.', ''),
                ])->all(),
            ])->all(),
        ]);
    }
}
