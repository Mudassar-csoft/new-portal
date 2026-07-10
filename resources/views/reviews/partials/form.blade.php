@php
    $review = $review ?? new \App\Models\Review();
    $statusOptions = $statusOptions ?? ['active' => 'Active', 'inactive' => 'Inactive'];
    $selectedStatus = old('status', $review->status ?: 'active');
    $selectedRating = old('rating', $review->rating ?: 5);
@endphp

@include('partials.validation-errors-alert')

<div class="form-row mt-1">
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $review->name) }}" placeholder="Enter name" required>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Designation</label>
        <input type="text" name="designation" class="form-control" value="{{ old('designation', $review->designation) }}" placeholder="Enter designation" required>
    </div>
    <div class="form-group col-lg-2 col-md-6">
        <label class="form-label required">Rating</label>
        <select name="rating" class="form-control" required>
            @foreach([1, 2, 3, 4, 5] as $rating)
                <option value="{{ $rating }}" @selected((int) $selectedRating === $rating)>{{ $rating }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-lg-2 col-md-6">
        <label class="form-label">Display Order</label>
        <input type="number" min="0" name="display_order" class="form-control" value="{{ old('display_order', $review->display_order ?? 0) }}">
    </div>
    <div class="form-group col-lg-2 col-md-6">
        <label class="form-label required">Status</label>
        <select name="status" class="form-control" required>
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-lg-6 col-md-6">
        <label class="form-label">Profile Image</label>
        <label class="review-upload" for="review-profile-image-upload">
            <input type="file" id="review-profile-image-upload" name="profile_image" accept="image/jpeg,image/png,image/webp">
            <span class="review-upload-icon" aria-hidden="true"><i class="fa fa-cloud-upload"></i></span>
            <span class="review-upload-copy">
                <span class="review-upload-title" data-review-upload-label>Choose profile image</span>
                <span class="review-upload-hint">JPG, PNG, WEBP up to 2 MB</span>
            </span>
        </label>
        @if($review->profile_image)
            <img class="review-current-image" src="{{ asset('storage/' . $review->profile_image) }}" alt="{{ $review->name }}">
            <div class="checkbox mt-2">
                <input type="hidden" name="remove_profile_image" value="0">
                <label class="form-label">
                    <input type="checkbox" name="remove_profile_image" value="1" @checked(old('remove_profile_image'))>
                    Remove current image
                </label>
            </div>
        @endif
    </div>
    <div class="form-group col-lg-6 col-md-6">
        <label class="form-label">Featured</label>
        <div class="checkbox review-checkbox">
            <input type="hidden" name="featured" value="0">
            <label class="form-label">
                <input type="checkbox" name="featured" value="1" @checked(old('featured', $review->featured))>
                Mark as featured
            </label>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="form-label required">Review</label>
    <textarea class="form-control" name="review" rows="6" placeholder="Enter review" required>{{ old('review', $review->review) }}</textarea>
</div>
