<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $scope = (string) $request->query('scope', 'all');
        $perPage = $this->resolvePerPage($request);

        $newsQuery = News::query()->with('campus:id,code,name,city');

        $this->applyScope($newsQuery, $scope);
        $this->applyFilters($newsQuery, $request);

        $newsItems = $newsQuery
            ->orderByDesc('news_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('news.index', [
            'newsItems' => $newsItems,
            'campuses' => $this->campusOptions(),
            'scopeCards' => $this->buildScopeCards($request),
            'filters' => [
                'scope' => $scope,
                'campus_id' => $request->integer('campus_id') ?: null,
                'status' => $request->input('status'),
                'search' => $request->input('search'),
                'per_page' => $perPage,
            ],
            'pageTitle' => $this->resolvePageTitle($scope),
        ]);
    }

    public function create(): View
    {
        return view('news.create', $this->formPayload(new News()));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateNews($request);

        News::query()->create($this->extractNewsData($request, $validated));

        return redirect()->route('news.index')->with('status', 'News created successfully.');
    }

    public function edit(News $news): View
    {
        return view('news.edit', $this->formPayload($news));
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $validated = $this->validateNews($request);

        $news->update($this->extractNewsData($request, $validated, $news));

        return redirect()->route('news.index')->with('status', 'News updated successfully.');
    }

    public function destroy(News $news): RedirectResponse
    {
        if ($news->featured_image_path) {
            Storage::disk('public')->delete($news->featured_image_path);
        }

        $news->delete();

        return redirect()->route('news.index')->with('status', 'News deleted successfully.');
    }

    public function toggleStatus(News $news): RedirectResponse
    {
        $news->update([
            'status' => $news->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()->route('news.index')->with(
            'status',
            $news->status === 'active' ? 'News activated.' : 'News suspended.'
        );
    }

    private function formPayload(News $news): array
    {
        return [
            'news' => $news,
            'campuses' => $this->campusOptions(),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
        ];
    }

    private function validateNews(Request $request): array
    {
        return $request->validate([
            'campus_id' => ['required', 'integer', 'exists:campuses,id'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:500'],
            'full_description' => ['required', 'string'],
            'news_date' => ['required', 'date'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_featured_image' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function extractNewsData(Request $request, array $validated, ?News $news = null): array
    {
        $imagePath = $news?->featured_image_path;

        if ($request->boolean('remove_featured_image') && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        if ($request->hasFile('featured_image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('featured_image')->store('news/featured-images', 'public');
        }

        return [
            'campus_id' => (int) $validated['campus_id'],
            'title' => trim((string) $validated['title']),
            'short_description' => trim((string) $validated['short_description']),
            'full_description' => $validated['full_description'],
            'news_date' => $validated['news_date'],
            'featured_image_path' => $imagePath,
            'status' => $validated['status'],
        ];
    }

    private function applyScope(Builder $query, string $scope): void
    {
        if (in_array($scope, ['active', 'inactive'], true)) {
            $query->where('status', $scope);
        }
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query
            ->when($request->integer('campus_id'), fn (Builder $builder, int $id) => $builder->where('campus_id', $id))
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->input('status')))
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $keyword = trim((string) $request->input('search'));

                $builder->where(function (Builder $inner) use ($keyword) {
                    $inner
                        ->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('short_description', 'like', '%' . $keyword . '%')
                        ->orWhere('full_description', 'like', '%' . $keyword . '%')
                        ->orWhereHas('campus', function (Builder $campusQuery) use ($keyword) {
                            $campusQuery
                                ->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('code', 'like', '%' . $keyword . '%')
                                ->orWhere('city', 'like', '%' . $keyword . '%');
                        });
                });
            });
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
    }

    private function buildScopeCards(Request $request): array
    {
        $counts = News::query()
            ->tap(fn (Builder $query) => $this->applyFilters($query, $request))
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            ['scope' => 'all', 'label' => 'All News', 'count' => (int) $counts->sum()],
            ['scope' => 'active', 'label' => 'Active', 'count' => (int) ($counts['active'] ?? 0)],
            ['scope' => 'inactive', 'label' => 'Inactive', 'count' => (int) ($counts['inactive'] ?? 0)],
        ];
    }

    private function resolvePageTitle(string $scope): string
    {
        return match ($scope) {
            'active' => 'Active News',
            'inactive' => 'Inactive News',
            default => 'News Management',
        };
    }

    private function campusOptions()
    {
        return Campus::query()->orderBy('name')->get(['id', 'code', 'name', 'city']);
    }
}
