/**
 * Auto-Logout System — Admin & Keagamaan
 * Sesi berakhir otomatis setelah tidak aktif selama batas waktu (default 10 menit).
 */

(function () {
    'use strict';

    function readMeta(name, fallback) {
        var el = document.querySelector('meta[name="' + name + '"]');
        return el && el.content ? el.content : fallback;
    }

    function isIdleSessionEnabled() {
        return readMeta('session-idle-enabled', 'false') === 'true';
    }

    // Configuration (overridable via meta tags di layout admin/keagamaan)
    var INACTIVITY_LIMIT = parseInt(readMeta('session-idle-minutes', '10'), 10) || 10;
    var WARNING_TIME = 2; // menit sebelum logout — tampilkan peringatan
    var LOGOUT_URL = readMeta('logout-url', '/logout');

    var inactivityTime = 0;
    var warningShown = false;
    var logoutTimer = null;
    var warningTimer = null;
    var countdownTimer = null;
    var timeRemaining = 0;
    var isInitialized = false;
    var logoutRedirectStarted = false;
    var logoutFormSubmitted = false;
    var isPaused = false;

    function clearAllTimers() {
        if (logoutTimer) {
            clearTimeout(logoutTimer);
            logoutTimer = null;
        }
        if (warningTimer) {
            clearTimeout(warningTimer);
            warningTimer = null;
        }
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
    }

    function isAutoLogoutSwal(title) {
        if (!title) return false;
        var t = title.toLowerCase();
        return t.indexOf('peringatan inaktivitas') >= 0 ||
            t.indexOf('sesi berakhir') >= 0 ||
            t.indexOf('sesi dilanjutkan') >= 0;
    }

    function closeSwalIfVisible() {
        if (!window.Swal || !Swal.isVisible()) return;

        var titleElement = document.querySelector('.swal2-popup .swal2-title');
        if (titleElement && isAutoLogoutSwal(titleElement.textContent)) {
            Swal.close();
        }
    }

    function isUserModalOpen() {
        if (!window.Swal || !Swal.isVisible()) return false;

        var titleElement = document.querySelector('.swal2-popup .swal2-title');
        if (!titleElement) return false;

        return !isAutoLogoutSwal(titleElement.textContent);
    }

    function resetInactivityTimer() {
        if (!isInitialized || logoutRedirectStarted || isPaused) return;
        if (isUserModalOpen()) return;

        inactivityTime = 0;
        warningShown = false;
        clearAllTimers();
        closeSwalIfVisible();
        localStorage.setItem('lastActivity', Date.now().toString());
        startInactivityCheck();
    }

    function startInactivityCheck() {
        if (!isInitialized || logoutRedirectStarted) return;

        clearAllTimers();

        logoutTimer = setTimeout(function () {
            if (!logoutRedirectStarted && !warningShown) {
                performAutoLogout();
            }
        }, INACTIVITY_LIMIT * 60 * 1000);

        if (INACTIVITY_LIMIT > WARNING_TIME) {
            warningTimer = setTimeout(function () {
                if (!isInitialized || logoutRedirectStarted || warningShown) return;
                showWarningDialog();
            }, (INACTIVITY_LIMIT - WARNING_TIME) * 60 * 1000);
        }
    }

    function showWarningDialog() {
        if (warningShown || logoutRedirectStarted) return;

        warningShown = true;
        timeRemaining = WARNING_TIME * 60;

        if (logoutTimer) {
            clearTimeout(logoutTimer);
            logoutTimer = null;
        }
        if (warningTimer) {
            clearTimeout(warningTimer);
            warningTimer = null;
        }

        Swal.fire({
            title: 'Peringatan Inaktivitas',
            html: '<div class="text-center">' +
                '<p class="text-gray-600 mb-4">Anda tidak memiliki aktivitas selama beberapa waktu.</p>' +
                '<p class="text-gray-700 mb-2">Anda akan otomatis logout dalam:</p>' +
                '<div class="text-4xl font-bold text-red-600 mb-4" id="countdown">' + formatTime(timeRemaining) + '</div>' +
                '<p class="text-sm text-gray-500">Klik tombol di bawah untuk melanjutkan sesi</p>' +
                '</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Lanjutkan Sesi',
            cancelButtonText: 'Logout Sekarang',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#dc2626',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(function (result) {
            if (result.isConfirmed) {
                handleContinueSession();
            } else {
                handleLogoutNow();
            }
        });

        countdownTimer = setInterval(function () {
            if (logoutRedirectStarted) {
                clearInterval(countdownTimer);
                countdownTimer = null;
                return;
            }

            timeRemaining--;
            var countdownElement = document.getElementById('countdown');
            if (countdownElement) {
                countdownElement.textContent = formatTime(timeRemaining);
            }

            if (timeRemaining <= 0) {
                clearInterval(countdownTimer);
                countdownTimer = null;
                closeSwalIfVisible();
                performAutoLogout();
            }
        }, 1000);
    }

    function handleContinueSession() {
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }

        warningShown = false;
        timeRemaining = 0;
        localStorage.setItem('lastActivity', Date.now().toString());
        closeSwalIfVisible();

        Swal.fire({
            icon: 'success',
            title: 'Sesi Dilanjutkan',
            text: 'Sesi Anda telah diperpanjang. Silakan lanjutkan aktivitas Anda.',
            timer: 2000,
            showConfirmButton: false,
            timerProgressBar: true,
            didClose: function () {
                startInactivityCheck();
            }
        });
    }

    function handleLogoutNow() {
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }

        warningShown = false;
        timeRemaining = 0;
        closeSwalIfVisible();
        performLogout();
    }

    function formatTime(seconds) {
        var mins = Math.floor(Math.max(0, seconds) / 60);
        var secs = Math.max(0, seconds) % 60;
        return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }

    function performAutoLogout() {
        if (logoutRedirectStarted) return;

        logoutRedirectStarted = true;
        clearAllTimers();
        closeSwalIfVisible();

        Swal.fire({
            title: 'Sesi Berakhir',
            html: '<div class="text-center">' +
                '<p class="text-gray-600 mb-4">Anda telah logout secara otomatis karena tidak ada aktivitas selama ' + INACTIVITY_LIMIT + ' menit.</p>' +
                '<p class="text-sm text-gray-500">Mengalihkan ke halaman login...</p>' +
                '</div>',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            timer: 2500,
            timerProgressBar: true,
            willClose: function () {
                performLogout();
            }
        });

        setTimeout(function () {
            performLogout();
        }, 3000);
    }

    function performLogout() {
        if (logoutFormSubmitted) return;

        logoutFormSubmitted = true;
        logoutRedirectStarted = true;
        clearAllTimers();
        closeSwalIfVisible();

        localStorage.removeItem('lastActivity');
        localStorage.removeItem('sessionStartTime');

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = LOGOUT_URL;
        form.setAttribute('data-auto-logout-form', 'true');

        var csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf && csrf.content) {
            var tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf.content;
            form.appendChild(tokenInput);
        }

        document.body.appendChild(form);
        form.submit();
    }

    function handleSessionExpiredResponse(response) {
        if (response.status !== 401) return false;

        return response.clone().json().then(function (data) {
            if (data && data.session_expired && data.redirect_url) {
                logoutRedirectStarted = true;
                clearAllTimers();
                window.location.href = data.redirect_url;
                return true;
            }
            return false;
        }).catch(function () {
            return false;
        });
    }

    function installFetchInterceptor() {
        if (window.__idleSessionFetchPatched) return;
        window.__idleSessionFetchPatched = true;

        var originalFetch = window.fetch.bind(window);
        window.fetch = function () {
            return originalFetch.apply(window, arguments).then(function (response) {
                handleSessionExpiredResponse(response);
                return response;
            });
        };
    }

    function initAutoLogout() {
        if (isInitialized || !isIdleSessionEnabled()) return;

        isInitialized = true;
        logoutRedirectStarted = false;
        localStorage.setItem('lastActivity', Date.now().toString());
        localStorage.setItem('sessionStartTime', Date.now().toString());

        var activityEvents = ['mousedown', 'mousemove', 'keypress', 'keydown', 'scroll', 'touchstart', 'click'];
        activityEvents.forEach(function (event) {
            document.addEventListener(event, resetInactivityTimer, { passive: true });
        });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden || !isInitialized || logoutRedirectStarted) return;

            var lastActivity = localStorage.getItem('lastActivity');
            if (!lastActivity) {
                resetInactivityTimer();
                return;
            }

            var inactiveMinutes = (Date.now() - parseInt(lastActivity, 10)) / 1000 / 60;
            if (inactiveMinutes >= INACTIVITY_LIMIT) {
                performAutoLogout();
            } else {
                resetInactivityTimer();
            }
        });

        installFetchInterceptor();
        startInactivityCheck();

        window.pauseAutoLogoutReset = function () {
            isPaused = true;
        };

        window.resumeAutoLogoutReset = function () {
            isPaused = false;
            resetInactivityTimer();
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAutoLogout);
    } else {
        initAutoLogout();
    }
})();
