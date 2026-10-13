@php
    $remarksCharacterCount = mb_strlen((string) $value);
@endphp

<small id="{{ $inputId }}-counter" class="{{ $remarksCharacterCount < 250 ? 'text-danger' : 'text-muted' }} d-block mb-1" data-remarks-for="{{ $inputId }}" role="status" aria-live="polite" aria-atomic="true">
    <span data-remarks-written>{{ $remarksCharacterCount }}</span> characters written
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
                            counter.querySelector('[data-remarks-written]').textContent = written;
                            counter.classList.toggle('text-danger', written < 250);
                            counter.classList.toggle('text-muted', written >= 250);
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
