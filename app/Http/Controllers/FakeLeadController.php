<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Program;
use App\Models\WebLead;
use Faker\Factory as FakerFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FakeLeadController extends Controller
{
    private const SOURCES = [
        WebLead::SOURCE_QUICK_LEAD,
        WebLead::SOURCE_WEBSITE_ENROLLMENT,
        WebLead::SOURCE_WEBSITE_ADMISSION,
        WebLead::SOURCE_BROCHURE_DOWNLOAD,
    ];

    private const CITIES = [
        ['Faisalabad', 'Pakistan'],
        ['Lahore', 'Pakistan'],
        ['Karachi', 'Pakistan'],
        ['Islamabad', 'Pakistan'],
        ['Multan', 'Pakistan'],
        ['Rawalpindi', 'Pakistan'],
        ['Peshawar', 'Pakistan'],
        ['Quetta', 'Pakistan'],
    ];

    /**
     * GET /api/leads/feed
     * Returns the recent pending web leads as JSON, grouped by source type.
     * Same shape as what the bell-notification view consumes.
     */
    public function feed(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->query('limit', 10), 100));

        $sources = WebLead::sourceLabels();
        $payload = [];

        foreach (array_keys($sources) as $sourceType) {
            $payload[$sourceType] = [
                'label' => $sources[$sourceType],
                'count' => WebLead::query()->pending()->ofSource($sourceType)->count(),
                'items' => WebLead::query()
                    ->pending()
                    ->ofSource($sourceType)
                    ->latest('submitted_at')
                    ->latest('id')
                    ->take($limit)
                    ->get(['id', 'source_type', 'full_name', 'email', 'phone', 'city',
                        'interested_program', 'preferred_campus', 'submitted_at', 'status'])
                    ->map(fn ($lead) => [
                        'id' => $lead->id,
                        'source_type' => $lead->source_type,
                        'full_name' => $lead->full_name,
                        'email' => $lead->email,
                        'phone' => $lead->phone,
                        'city' => $lead->city,
                        'interested_program' => $lead->interested_program,
                        'preferred_campus' => $lead->preferred_campus,
                        'submitted_at' => optional($lead->submitted_at)->toIso8601String(),
                        'status' => $lead->status,
                    ])
                    ->all(),
            ];
        }

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'sources' => $payload,
            'total_pending' => array_sum(array_column($payload, 'count')),
        ]);
    }

    /**
     * POST /api/leads/generate-fake
     * Generates N fake WebLead records distributed across all source types.
     */
    public function generate(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'count' => ['nullable', 'integer', 'min:1', 'max:100'],
            'source' => ['nullable', 'string', 'in:' . implode(',', self::SOURCES) . ',all'],
        ]);

        $count = $validated['count'] ?? 8;
        $source = $validated['source'] ?? 'all';

        $faker = FakerFactory::create('en_US');
        $programs = Program::query()->inRandomOrder()->pluck('title', 'id')->all()
            ?: ['Microsoft Office Management', 'Full Stack Developer', 'Data Science', 'WordPress', 'Cyber Security'];
        $campuses = Campus::query()->inRandomOrder()->pluck('name')->all()
            ?: ['Faisalabad', 'Lahore', 'Karachi'];

        $created = [];

        for ($i = 0; $i < $count; $i++) {
            $sourceType = $source === 'all'
                ? self::SOURCES[array_rand(self::SOURCES)]
                : $source;

            [$city, $country] = self::CITIES[array_rand(self::CITIES)];

            $weblead = WebLead::create([
                'source_type' => $sourceType,
                'source_site' => 'career.edu.pk',
                'full_name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => '03' . $faker->numberBetween(10, 99) . $faker->numerify('#######'),
                'country' => $country,
                'city' => $city,
                'area' => $faker->streetName(),
                'interested_program' => is_string($programs[array_rand($programs)] ?? null)
                    ? $programs[array_rand($programs)]
                    : (string) ($programs[array_rand($programs)] ?? 'General'),
                'preferred_campus' => $campuses[array_rand($campuses)] ?? null,
                'teaching_method' => $faker->randomElement(['on-campus', 'online', 'hybrid']),
                'gender' => $faker->randomElement(['male', 'female']),
                'message' => $faker->sentence(8),
                'payload' => [],
                'status' => WebLead::STATUS_NEW,
                'submitted_at' => Carbon::now()->subMinutes(rand(0, 1440)),
            ]);

            $created[] = $weblead->id;
        }

        $message = sprintf('Generated %d fake web lead%s.', count($created), count($created) === 1 ? '' : 's');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'created_ids' => $created,
                'count' => count($created),
            ]);
        }

        return back()->with('status', $message);
    }
}
