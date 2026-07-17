<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $scope = (string) $request->query('scope', 'active');
        $activeScope = in_array($scope, ['active', 'inactive', 'featured', 'all'], true) ? $scope : 'active';

        if ($request->ajax()) {
            $query = Review::query()->select('reviews.*');
            $this->applyScope($query, $activeScope);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('profile_image', function (Review $review) {
                    if (!$review->profile_image) {
                        return '<span class="review-thumb review-thumb-empty"><i class="fa fa-image"></i></span>';
                    }

                    return sprintf(
                        '<img class="review-thumb" src="%s" alt="%s">',
                        e(asset('storage/' . $review->profile_image)),
                        e($review->name)
                    );
                })
                ->editColumn('name', fn (Review $review) => e($review->name))
                ->editColumn('designation', fn (Review $review) => e($review->designation))
                ->editColumn('review', fn (Review $review) => e(str($review->review)->limit(90)))
                ->editColumn('rating', fn (Review $review) => e($review->rating . '/5'))
                ->editColumn('display_order', fn (Review $review) => e((string) $review->display_order))
                ->editColumn('featured', function (Review $review) {
                    $class = $review->featured ? 'label-success' : 'label-default';
                    $label = $review->featured ? 'Featured' : 'Normal';

                    return '<span class="label ' . $class . '">' . e($label) . '</span>';
                })
                ->editColumn('status', function (Review $review) {
                    $class = $review->status === 'active' ? 'label-success' : 'label-default';

                    return '<span class="label ' . $class . '">' . e(ucfirst($review->status)) . '</span>';
                })
                ->addColumn('date', fn (Review $review) => optional($review->created_at)->format('d-M-Y') ?? 'N/A')
                ->addColumn('actions', fn (Review $review) => view('reviews.partials.action', ['review' => $review])->render())
                ->filter(function (Builder $query) use ($request): void {
                    $keyword = trim((string) data_get($request->input('search', []), 'value', ''));

                    if ($keyword === '') {
                        return;
                    }

                    $like = $this->toSqlLikePattern($keyword);

                    $query->where(function (Builder $searchQuery) use ($like): void {
                        $searchQuery
                            ->where('name', 'like', $like)
                            ->orWhere('designation', 'like', $like)
                            ->orWhere('review', 'like', $like)
                            ->orWhere('rating', 'like', $like)
                            ->orWhere('display_order', 'like', $like)
                            ->orWhere('status', 'like', $like);
                    });
                })
                ->rawColumns(['profile_image', 'featured', 'status', 'actions'])
                ->make(true);
        }

        return view('reviews.index', [
            'activeScope' => $activeScope,
            'scopeCards' => $this->buildScopeCards(),
        ]);
    }

    public function create(): View
    {
        return view('reviews.create', $this->formPayload(new Review()));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateReview($request);

        Review::query()->create($this->extractReviewData($request, $validated));

        return redirect()->route('reviews.index')->with('status', 'Review created successfully.');
    }

    public function edit(Review $review): View
    {
        return view('reviews.edit', $this->formPayload($review));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $validated = $this->validateReview($request);

        $review->update($this->extractReviewData($request, $validated, $review));

        return redirect()->route('reviews.index')->with('status', 'Review updated successfully.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        if ($review->profile_image) {
            Storage::disk('public')->delete($review->profile_image);
        }

        $review->delete();

        return redirect()->route('reviews.index')->with('status', 'Review deleted successfully.');
    }

    public function toggleStatus(Review $review): RedirectResponse
    {
        $review->update([
            'status' => $review->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()->route('reviews.index')->with(
            'status',
            $review->status === 'active' ? 'Review activated.' : 'Review suspended.'
        );
    }

    public function toggleFeatured(Review $review): RedirectResponse
    {
        $review->update([
            'featured' => !$review->featured,
        ]);

        return redirect()->route('reviews.index')->with(
            'status',
            $review->featured ? 'Review marked as featured.' : 'Review removed from featured.'
        );
    }

    private function formPayload(Review $review): array
    {
        return [
            'review' => $review,
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
        ];
    }

    private function validateReview(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'review' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_image' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function extractReviewData(Request $request, array $validated, ?Review $review = null): array
    {
        $imagePath = $review?->profile_image;

        if ($request->boolean('remove_profile_image') && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        if ($request->hasFile('profile_image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('profile_image')->store('reviews/profile-images', 'public');
        }

        return [
            'name' => trim((string) $validated['name']),
            'designation' => trim((string) $validated['designation']),
            'review' => trim((string) $validated['review']),
            'rating' => (int) $validated['rating'],
            'display_order' => (int) ($validated['display_order'] ?? 0),
            'featured' => $request->boolean('featured'),
            'profile_image' => $imagePath,
            'status' => $validated['status'],
        ];
    }

    private function applyScope(Builder $query, string $scope): void
    {
        if ($scope === 'featured') {
            $query->where('featured', true);
            return;
        }

        if (in_array($scope, ['active', 'inactive'], true)) {
            $query->where('status', $scope);
        }
    }

    private function buildScopeCards(): array
    {
        $statusCounts = Review::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $featuredCount = Review::query()->where('featured', true)->count();

        return [
            ['scope' => 'active', 'label' => 'Active Reviews', 'count' => (int) ($statusCounts['active'] ?? 0)],
            ['scope' => 'inactive', 'label' => 'Inactive', 'count' => (int) ($statusCounts['inactive'] ?? 0)],
            ['scope' => 'featured', 'label' => 'Featured', 'count' => (int) $featuredCount],
            ['scope' => 'all', 'label' => 'All Reviews', 'count' => (int) $statusCounts->sum()],
        ];
    }

    private function toSqlLikePattern(string $value): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value) . '%';
    }
}
