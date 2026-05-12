<script>
    (function () {
        function revealCampusFormPage() {
            setTimeout(function () {
                document.body.classList.add('campus-form-ready');
            }, 200);
        }

        function debounce(fn, delay) {
            var timer = null;
            return function () {
                var args = arguments;
                var ctx = this;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    fn.apply(ctx, args);
                }, delay);
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            revealCampusFormPage();

            if (window.CountryCityLoader) {
                CountryCityLoader.init('country-select', 'city-select', {
                    country: @json(old('country', $campus->country ?: 'Pakistan')),
                    city: @json(old('city', $campus->city ?: 'Faisalabad'))
                });
            }

            var abbrInput = document.getElementById('campus-city-abbr');
            var codeField = document.getElementById('campus-code-preview');
            var typeInputs = document.querySelectorAll('input[name="campus_type"]');
            var royaltyField = document.getElementById('royalty-rate');
            var royaltyContainer = royaltyField ? royaltyField.closest('.js-royalty-field') : null;

            function updateRoyaltyState() {
                var isFranchise = Array.from(typeInputs).some(function (input) {
                    return input.checked && input.value === 'franchise';
                });

                if (royaltyContainer) {
                    if (isFranchise) {
                        royaltyContainer.removeAttribute('hidden');
                    } else {
                        royaltyContainer.setAttribute('hidden', 'hidden');
                    }
                }

                if (royaltyField) {
                    royaltyField.disabled = !isFranchise;
                    if (!isFranchise) {
                        royaltyField.value = '';
                    }
                }
            }

            function updateCodePreview() {
                if (!abbrInput || !codeField) {
                    return;
                }

                var abbr = (abbrInput.value || '').toUpperCase().replace(/[^A-Z]/g, '').slice(0, 10);
                var baseUrl = codeField.getAttribute('data-count-url');
                var ignoreId = codeField.getAttribute('data-ignore-id');
                var currentCode = codeField.getAttribute('data-current-code') || '';

                abbrInput.value = abbr;

                if (!abbr || !baseUrl) {
                    codeField.value = currentCode || 'Auto generated on save';
                    return;
                }

                var url = baseUrl + '/' + encodeURIComponent(abbr);
                if (ignoreId) {
                    url += '?ignore_id=' + encodeURIComponent(ignoreId);
                }

                fetch(url)
                    .then(function (response) {
                        return response.ok ? response.json() : null;
                    })
                    .then(function (payload) {
                        if (!payload || !payload.next_code) {
                            return;
                        }

                        codeField.value = payload.next_code;
                    })
                    .catch(function () {
                        codeField.value = currentCode || 'Auto generated on save';
                    });
            }

            var debouncedCodePreview = debounce(updateCodePreview, 250);

            if (abbrInput) {
                abbrInput.addEventListener('input', debouncedCodePreview);
                updateCodePreview();
            }

            typeInputs.forEach(function (input) {
                input.addEventListener('change', updateRoyaltyState);
            });

            updateRoyaltyState();
        });
    })();
</script>
