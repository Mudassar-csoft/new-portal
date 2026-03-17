<script>
    (function () {
        function updateDiscountButtons(container) {
            var rows = container.querySelectorAll('[data-discount-row]');

            rows.forEach(function (row) {
                var removeButton = row.querySelector('[data-remove-discount]');

                if (removeButton) {
                    removeButton.disabled = rows.length === 1;
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            var container = document.getElementById('program-discount-rows');
            var addButton = document.getElementById('add-program-discount');
            var template = document.getElementById('program-discount-template');

            if (!container || !addButton || !template) {
                return;
            }

            addButton.addEventListener('click', function () {
                var nextIndex = Number(container.getAttribute('data-next-index') || container.children.length);
                var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
                var wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                container.appendChild(wrapper.firstChild);
                container.setAttribute('data-next-index', String(nextIndex + 1));
                updateDiscountButtons(container);
            });

            container.addEventListener('click', function (event) {
                if (!event.target.matches('[data-remove-discount]')) {
                    return;
                }

                var row = event.target.closest('[data-discount-row]');
                if (row) {
                    row.remove();
                    updateDiscountButtons(container);
                }
            });

            updateDiscountButtons(container);
        });
    })();
</script>
