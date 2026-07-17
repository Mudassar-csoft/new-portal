@php
    $news = $news ?? new \App\Models\News();
    $statusOptions = $statusOptions ?? ['active' => 'Active', 'inactive' => 'Inactive'];
    $selectedStatus = old('status', $news->status ?: 'active');
    $selectedCampus = old('campus_id', $news->campus_id);
@endphp

@include('partials.validation-errors-alert')

<div class="form-row mt-1">
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Campus</label>
        <select class="form-control" name="campus_id" required>
            <option value="">- Select -</option>
            @foreach($campuses as $campus)
                <option value="{{ $campus->id }}" @selected((string) $selectedCampus === (string) $campus->id)>
                    {{ $campus->name }}{{ $campus->city ? ' (' . $campus->city . ')' : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">News Title</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $news->title) }}" placeholder="Enter news title" required>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">News Date</label>
        <input type="date" name="news_date" class="form-control" value="{{ old('news_date', optional($news->news_date)->format('Y-m-d')) }}" required>
    </div>
    <div class="form-group col-lg-3 col-md-6">
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
        <label class="form-label required">Featured Image</label>
        <label class="news-upload" for="news-featured-image-upload">
            <input type="file" id="news-featured-image-upload" name="featured_image" accept="image/jpeg,image/png,image/webp">
            <span class="news-upload-icon" aria-hidden="true"><i class="fa fa-cloud-upload"></i></span>
            <span class="news-upload-copy">
                <span class="news-upload-title" data-news-upload-label>Choose featured image</span>
                <span class="news-upload-hint">JPG, PNG, WEBP up to 5 MB</span>
            </span>
        </label>
        @if($news->featured_image_path)
            <img class="news-current-image" src="{{ asset('storage/' . $news->featured_image_path) }}" alt="{{ $news->title }}">
            <div class="checkbox mt-2">
                <input type="hidden" name="remove_featured_image" value="0">
                <label class="form-label">
                    <input type="checkbox" name="remove_featured_image" value="1" @checked(old('remove_featured_image'))>
                    Remove current image
                </label>
            </div>
        @endif
    </div>
    <div class="form-group col-lg-6 col-md-6">
        <label class="form-label required">Short Description</label>
        <textarea class="form-control" name="short_description" rows="3" maxlength="500" placeholder="Enter short summary" required>{{ old('short_description', $news->short_description) }}</textarea>
    </div>
</div>

<div class="form-group">
    <label class="form-label required">Full Description</label>
    <textarea class="form-control js-news-editor" name="full_description" rows="8" placeholder="Enter full news detail" required>{{ old('full_description', $news->full_description) }}</textarea>
</div>
