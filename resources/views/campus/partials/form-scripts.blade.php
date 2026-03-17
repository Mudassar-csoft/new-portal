<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
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

            function updateRoyaltyState() {
                if (!royaltyField) {
                    return;
                }

                var isFranchise = Array.from(typeInputs).some(function (input) {
                    return input.checked && input.value === 'franchise';
                });

                royaltyField.disabled = !isFranchise;

                if (!isFranchise) {
                    royaltyField.value = '';
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

            if (abbrInput) {
                abbrInput.addEventListener('input', updateCodePreview);
                updateCodePreview();
            }

            typeInputs.forEach(function (input) {
                input.addEventListener('change', updateRoyaltyState);
            });

            updateRoyaltyState();
        });
    })();
</script>
