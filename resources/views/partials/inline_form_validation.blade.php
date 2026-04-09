<script>
    (function () {
        function disableNativeValidation(root) {
            if (!root || !root.querySelectorAll) return;

            root.querySelectorAll('form').forEach(function (form) {
                form.setAttribute('novalidate', 'novalidate');
            });
        }

        function escapeSelector(value) {
            if (window.CSS && typeof window.CSS.escape === 'function') {
                return window.CSS.escape(value);
            }

            return String(value).replace(/("|'|\\|\.|\[|\]|,|=|:|#|\(|\)|\+)/g, '\\$1');
        }

        function getMessage(field) {
            var validity = field.validity || {};

            if (validity.valueMissing) {
                return 'This field is required.';
            }

            if (validity.typeMismatch) {
                if (field.type === 'email') {
                    return 'Please enter a valid email address.';
                }

                if (field.type === 'url') {
                    return 'Please enter a valid URL.';
                }
            }

            if (validity.patternMismatch) {
                return 'Please match the requested format.';
            }

            if (validity.tooShort) {
                return 'Please lengthen this text.';
            }

            if (validity.tooLong) {
                return 'Please shorten this text.';
            }

            if (validity.rangeUnderflow || validity.rangeOverflow || validity.stepMismatch) {
                return 'Please enter a valid value.';
            }

            return field.dataset.validationMessage || field.validationMessage || 'Please review this field.';
        }

        function getGroupFields(form, field) {
            if (!field.name) {
                return [field];
            }

            var selector = '[name="' + escapeSelector(field.name) + '"]';
            return Array.prototype.slice.call(form.querySelectorAll(selector));
        }

        function findContainer(field, groupFields) {
            var firstField = groupFields[0] || field;
            var select2 = firstField.nextElementSibling && firstField.nextElementSibling.classList && firstField.nextElementSibling.classList.contains('select2-container')
                ? firstField.nextElementSibling
                : null;

            if (select2) {
                return select2;
            }

            var wrappers = [
                '.choice-group',
                '.gender-options',
                '.radio-group',
                '.campus-type-options',
                '.form-group',
                '.col-12',
                '[class*="col-"]'
            ];

            for (var i = 0; i < wrappers.length; i += 1) {
                var wrapper = firstField.closest(wrappers[i]);
                if (wrapper) {
                    return wrapper;
                }
            }

            return firstField;
        }

        function clearClientErrors(form) {
            form.querySelectorAll('.field-error[data-client-error="true"]').forEach(function (node) {
                node.remove();
            });

            form.querySelectorAll('.is-invalid[data-client-invalid="true"]').forEach(function (node) {
                node.classList.remove('is-invalid');
                node.removeAttribute('data-client-invalid');
            });
        }

        function showFieldError(form, field) {
            var groupFields = getGroupFields(form, field);
            var container = findContainer(field, groupFields);

            if (!container) {
                return;
            }

            groupFields.forEach(function (item) {
                item.classList.add('is-invalid');
                item.setAttribute('data-client-invalid', 'true');
            });

            if (container.querySelector('.field-error[data-client-error="true"]')) {
                return;
            }

            var error = document.createElement('div');
            error.className = 'field-error';
            error.setAttribute('data-client-error', 'true');
            error.textContent = getMessage(field);

            container.appendChild(error);
        }

        function bindForm(form) {
            if (!form || form.dataset.inlineValidationBound === 'true') {
                return;
            }

            form.dataset.inlineValidationBound = 'true';
            form.setAttribute('novalidate', 'novalidate');

            form.addEventListener('submit', function (event) {
                clearClientErrors(form);

                if (form.checkValidity()) {
                    return;
                }

                event.preventDefault();

                var invalidFields = Array.prototype.slice.call(form.elements).filter(function (field) {
                    return field.willValidate && !field.validity.valid;
                });

                var handledNames = {};

                invalidFields.forEach(function (field) {
                    var key = field.name || field.id || ('field-' + Math.random());

                    if (handledNames[key]) {
                        return;
                    }

                    handledNames[key] = true;
                    showFieldError(form, field);
                });

                if (invalidFields[0] && typeof invalidFields[0].focus === 'function') {
                    invalidFields[0].focus();
                }
            });

            form.addEventListener('input', function (event) {
                var field = event.target;
                if (!field || !field.willValidate) {
                    return;
                }

                clearClientErrors(form);
            });

            form.addEventListener('change', function (event) {
                var field = event.target;
                if (!field || !field.willValidate) {
                    return;
                }

                clearClientErrors(form);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            disableNativeValidation(document);
            document.querySelectorAll('form').forEach(bindForm);

            if (!window.MutationObserver) {
                return;
            }

            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    mutation.addedNodes.forEach(function (node) {
                        if (!node || node.nodeType !== 1) {
                            return;
                        }

                        if (node.matches && node.matches('form')) {
                            bindForm(node);
                        }

                        disableNativeValidation(node);

                        if (node.querySelectorAll) {
                            node.querySelectorAll('form').forEach(bindForm);
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    })();
</script>
