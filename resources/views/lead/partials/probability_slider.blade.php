@php
    $inputName = $inputName ?? 'details[probability]';
    $inputId = $inputId ?? null;
    $displayId = $displayId ?? null;
    $value = $value ?? 0;
    $min = $min ?? 0;
    $max = $max ?? 100;
    $step = $step ?? 5;
    $required = $required ?? false;
    $errorKey = $errorKey ?? null;
    $showDisplay = $showDisplay ?? true;
    $numericValue = is_numeric($value) ? (float) $value : (float) $min;
    $numericValue = max((float) $min, min((float) $max, $numericValue));
    $progress = ((float) $max > (float) $min)
        ? (($numericValue - (float) $min) / ((float) $max - (float) $min)) * 100
        : 0;
@endphp

<div class="probability-field" style="--probability-progress: {{ round($progress, 2) }}%;">
    <div class="probability-shell{{ $errorKey && $errors->has($errorKey) ? ' has-error' : '' }}">
        <input
            type="range"
            name="{{ $inputName }}"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            value="{{ $numericValue }}"
            class="probability-input probability-range{{ $errorKey && $errors->has($errorKey) ? ' is-invalid' : '' }}"
            @if($inputId) id="{{ $inputId }}" @endif
            @if($displayId) data-probability-display-id="{{ $displayId }}" @endif
            @if($required) required @endif
        >

        <div class="probability-ticks" aria-hidden="true">
            @for ($tickIndex = 0; $tickIndex <= 30; $tickIndex++)
                <span class="{{ $tickIndex % 3 === 0 ? 'is-major' : '' }}"></span>
            @endfor
        </div>
    </div>

    <div class="probability-scale" aria-hidden="true">
        @for ($label = 0; $label <= 100; $label += 10)
            <span>{{ $label }}</span>
        @endfor
    </div>

    @if($showDisplay)
        <div class="probability-display">
            Probability: Selected <span @if($displayId) id="{{ $displayId }}" @endif>{{ $numericValue }}%</span>
        </div>
    @endif

    @if($errorKey && $errors->has($errorKey))
        <div class="field-error">{{ $errors->first($errorKey) }}</div>
    @endif
</div>
