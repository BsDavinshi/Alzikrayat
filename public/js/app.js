(function () {
    'use strict';

    function readMeta(metaName) {
        var metaElement = document.querySelector('meta[name="' + metaName + '"]');
        return metaElement ? metaElement.getAttribute('content') : '';
    }

    var Alz = {
        csrfToken: readMeta('csrf-token'),
        baseUrl: readMeta('base-url'),

        postForm: function (url, formData) {
            return fetch(url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': Alz.csrfToken,
                    'Accept': 'application/json'
                }
            }).then(function (response) {
                return response.json()
                    .catch(function () { return {}; })
                    .then(function (data) { return { ok: response.ok, status: response.status, data: data }; });
            });
        },

        toast: function (message, variant) {
            var toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            var toastElement = document.createElement('div');
            toastElement.className = 'toast align-items-center border-0 text-bg-' + (variant || 'success');
            toastElement.setAttribute('role', 'status');
            toastElement.innerHTML = '<div class="d-flex"><div class="toast-body"></div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
            toastElement.querySelector('.toast-body').textContent = message;
            toastContainer.appendChild(toastElement);

            var bootstrapToast = new bootstrap.Toast(toastElement, { delay: 3500 });
            toastElement.addEventListener('hidden.bs.toast', function () { toastElement.remove(); });
            bootstrapToast.show();
        }
    };
    window.Alz = Alz;

    function initThemeToggle() {
        var toggleButton = document.getElementById('themeToggle');
        if (!toggleButton) { return; }

        var rootElement = document.documentElement;
        var updateIcon = function () {
            var isDark = rootElement.getAttribute('data-bs-theme') === 'dark';
            toggleButton.innerHTML = isDark ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
        };

        toggleButton.addEventListener('click', function () {
            var nextTheme = rootElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            rootElement.setAttribute('data-bs-theme', nextTheme);
            try { localStorage.setItem('alz-theme', nextTheme); } catch (storageError) { }
            updateIcon();
        });
        updateIcon();
    }

    function initConfirmDialogs() {
        var modalElement = document.getElementById('confirmModal');
        if (!modalElement) { return; }

        var confirmModal = new bootstrap.Modal(modalElement);
        var pendingForm = null;

        document.addEventListener('submit', function (submitEvent) {
            var form = submitEvent.target;
            if (!form.hasAttribute('data-confirm') || form.dataset.confirmed === 'yes') { return; }

            submitEvent.preventDefault();
            submitEvent.stopImmediatePropagation();
            pendingForm = form;
            document.getElementById('confirmModalMessage').textContent = form.getAttribute('data-confirm');
            confirmModal.show();
        }, true);

        document.getElementById('confirmModalAccept').addEventListener('click', function () {
            if (!pendingForm) { return; }
            pendingForm.dataset.confirmed = 'yes';
            confirmModal.hide();
            if (typeof pendingForm.requestSubmit === 'function') {
                pendingForm.requestSubmit();
            } else {
                pendingForm.submit();
            }
            var submittedForm = pendingForm;
            pendingForm = null;
            setTimeout(function () { delete submittedForm.dataset.confirmed; }, 0);
        });
    }

    function initPasswordToggles() {
        document.querySelectorAll('[data-toggle-password]').forEach(function (toggleButton) {
            toggleButton.addEventListener('click', function () {
                var passwordInput = document.querySelector(toggleButton.getAttribute('data-toggle-password'));
                if (!passwordInput) { return; }
                var willShow = passwordInput.type === 'password';
                passwordInput.type = willShow ? 'text' : 'password';
                toggleButton.innerHTML = willShow ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            });
        });
    }

    function initCharacterCounters() {
        document.querySelectorAll('[data-counter]').forEach(function (field) {
            var counterElement = document.querySelector(field.getAttribute('data-counter'));
            if (!counterElement) { return; }
            var refresh = function () { counterElement.textContent = String(field.value.length); };
            field.addEventListener('input', refresh);
            refresh();
        });
    }

    function initStatCounters() {
        var counters = document.querySelectorAll('[data-count-to]');
        if (!counters.length || !('IntersectionObserver' in window)) { return; }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) { return; }
                observer.unobserve(entry.target);

                var targetValue = parseInt(entry.target.getAttribute('data-count-to'), 10) || 0;
                var startTime = null;
                var durationMs = 900;

                var step = function (timestamp) {
                    if (startTime === null) { startTime = timestamp; }
                    var progress = Math.min(1, (timestamp - startTime) / durationMs);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    entry.target.textContent = Math.round(targetValue * eased).toLocaleString();
                    if (progress < 1) { requestAnimationFrame(step); }
                };
                requestAnimationFrame(step);
            });
        }, { threshold: 0.4 });

        counters.forEach(function (counter) { observer.observe(counter); });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initThemeToggle();
        initConfirmDialogs();
        initPasswordToggles();
        initCharacterCounters();
        initStatCounters();
    });
})();
