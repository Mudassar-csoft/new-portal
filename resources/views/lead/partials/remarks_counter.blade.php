@php
    $remarksCharacterCount = mb_strlen((string) $value);
    $remarksRequiredCount = max(0, $minLength - mb_strlen(trim((string) $value)));
@endphp

<small id="{{ $inputId }}-counter" class="text-muted d-block mb-1" data-remarks-for="{{ $inputId }}" role="status" aria-live="polite" aria-atomic="true">
    <span data-remarks-written>{{ $remarksCharacterCount }}</span> characters written
    &middot; <span data-remarks-required>{{ $remarksRequiredCount > 0 ? $remarksRequiredCount . ' more characters required' : 'Minimum met' }}</span>
    @if(isset($maxLength))
        &middot; <span data-remarks-remaining>{{ max(0, $maxLength - $remarksCharacterCount) }}</span> characters remaining
    @endif
</small>

@once
    @push('scripts')
        <script>
            (function () {
                function bindRemarksCounters() {
                    document.querySelectorAll('[data-remarks-for]').forEach(function (counter) {
                        var textarea = document.getElementById(counter.getAttribute('data-remarks-for'));
                        if (!textarea) return;

                        function updateCounter() {
                            var written = Array.from(textarea.value).length;
                            var required = Math.max(0, textarea.minLength - Array.from(textarea.value.trim()).length);
                            counter.querySelector('[data-remarks-written]').textContent = written;
                            counter.querySelector('[data-remarks-required]').textContent = required > 0
                                ? required + ' more characters required'
                                : 'Minimum met';

                            var remaining = counter.querySelector('[data-remarks-remaining]');
                            if (remaining) remaining.textContent = Math.max(0, textarea.maxLength - written);
                        }

                        textarea.addEventListener('input', updateCounter);
                        if (textarea.form) {
                            textarea.form.addEventListener('reset', function () {
                                setTimeout(updateCounter, 0);
                            });
                        }
                        window.addEventListener('pageshow', updateCounter);
                        updateCounter();
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bindRemarksCounters);
                } else {
                    bindRemarksCounters();
                }
            })();
        </script>
    @endpush
@endonce
