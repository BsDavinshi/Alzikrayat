(function () {
    'use strict';

    var NAME_REGEX = /^\p{L}+(?: \p{L}+)*$/u;

    var EMAIL_REGEX = /^[A-Za-z0-9._%+\-]+@[A-Za-z0-9](?:[A-Za-z0-9\-]*[A-Za-z0-9])?(?:\.[A-Za-z0-9](?:[A-Za-z0-9\-]*[A-Za-z0-9])?)*\.[A-Za-z]{2,}$/;

    var IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    var ruleCheckers = {
        name: function (field) {
            return NAME_REGEX.test(field.value.trim()) ? '' : 'Letters only, up to 50 characters.';
        },
        email: function (field) {
            return EMAIL_REGEX.test(field.value.trim()) ? '' : 'Please enter a valid email address.';
        },
        password: function (field) {
            var value = field.value;
            if (value.length < 8) { return 'At least 8 characters.'; }
            if (!/[A-Za-z]/.test(value) || !/\d/.test(value)) { return 'Include at least one letter and one number.'; }
            return '';
        },
        image: function (field) {
            var chosenFile = field.files && field.files[0];
            if (!chosenFile) { return 'Please choose an image.'; }
            if (IMAGE_TYPES.indexOf(chosenFile.type) === -1) { return 'Only JPG, PNG, GIF or WEBP images are allowed.'; }
            var maxBytes = parseInt(field.getAttribute('data-max-bytes'), 10) || 5242880;
            if (chosenFile.size > maxBytes) { return 'The image is larger than ' + (maxBytes / 1048576).toFixed(1) + ' MB.'; }
            return '';
        }
    };

    function scorePassword(password) {
        var score = 0;
        if (password.length >= 8) { score++; }
        if (password.length >= 12) { score++; }
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) { score++; }
        if (/\d/.test(password) && /[^A-Za-z0-9]/.test(password)) { score++; }
        return score;
    }

    function validateField(field) {
        field.setCustomValidity('');

        var customMessage = '';
        var ruleName = field.getAttribute('data-rule');
        var isEmptyOptional = !field.required && field.value === '' && field.type !== 'file';

        if (field.validity.valid && ruleName && ruleCheckers[ruleName] && !isEmptyOptional) {
            customMessage = ruleCheckers[ruleName](field);
        }

        var matchSelector = field.getAttribute('data-match');
        if (!customMessage && matchSelector) {
            var otherField = document.querySelector(matchSelector);
            if (otherField && otherField.value !== field.value) {
                customMessage = 'Passwords must match.';
            }
        }

        field.setCustomValidity(customMessage);

        var isValid = field.checkValidity();
        field.classList.toggle('is-invalid', !isValid);
        field.classList.toggle('is-valid', isValid && field.value !== '');

        var feedbackElement = field.closest('.mb-3, .col-sm-6, .col-12, .card-body, form').querySelector('.invalid-feedback');
        if (feedbackElement && !isValid) {
            feedbackElement.textContent = customMessage || field.validationMessage;
        }
        return isValid;
    }

    function attachValidation(form) {
        var fields = form.querySelectorAll('input[name]:not([type=hidden]), textarea[name], select[name]');

        fields.forEach(function (field) {
            var eventName = field.type === 'file' ? 'change' : 'input';
            field.addEventListener(eventName, function () {
                if (form.classList.contains('was-submitted') || field.value !== '') {
                    validateField(field);
                }
                form.querySelectorAll('[data-match="#' + field.id + '"]').forEach(function (dependent) {
                    if (dependent.value !== '') { validateField(dependent); }
                });
            });

            var meterSelector = field.getAttribute('data-strength-meter');
            if (meterSelector) {
                var meterBar = document.querySelector(meterSelector);
                field.addEventListener('input', function () {
                    var strengthScore = scorePassword(field.value);
                    var colours = ['bg-danger', 'bg-danger', 'bg-warning', 'bg-info', 'bg-success'];
                    meterBar.style.width = (field.value ? Math.max(10, strengthScore * 25) : 0) + '%';
                    meterBar.className = 'progress-bar ' + colours[strengthScore];
                });
            }
        });

        form.addEventListener('submit', function (submitEvent) {
            form.classList.add('was-submitted');
            var allValid = true;
            fields.forEach(function (field) {
                if (!validateField(field)) { allValid = false; }
            });

            if (!allValid) {
                submitEvent.preventDefault();
                submitEvent.stopImmediatePropagation();
                var firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) { firstInvalid.focus({ preventScroll: false }); }
            }
        });
    }

    window.AlzValidation = { validateField: validateField };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-validate]').forEach(attachValidation);
    });
})();
