/**
 * Auto-Logout System
 * Automatically logs out users after 10 minutes of inactivity
 */

(function() {
    'use strict';

    // Configuration
    const INACTIVITY_LIMIT = 10; // minutes
    const WARNING_TIME = 2; // minutes before logout (show warning)
    const CHECK_INTERVAL = 1000; // check every second

    // State variables
    let inactivityTime = 0;
    let warningShown = false;
    let logoutTimer = null;
    let warningTimer = null; // NEW: Track warning timer separately
    let countdownTimer = null;
    let timeRemaining = 0;
    let isInitialized = false; // NEW: Track initialization state
    let isLogoutInProgress = false; // NEW: Prevent multiple logout attempts

    /**
     * Clear all timers
     */
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

    /**
     * Close SweetAlert if visible
     */
    function closeSwalIfVisible() {
        if (window.Swal && Swal.isVisible()) {
            Swal.close();
        }
    }

    /**
     * Reset inactivity timer on user activity
     */
    function resetInactivityTimer() {
        // Don't reset if not initialized or logout is in progress
        if (!isInitialized || isLogoutInProgress) {
            return;
        }

        inactivityTime = 0;
        warningShown = false;

        // Clear all timers
        clearAllTimers();

        // Close any open SweetAlert
        closeSwalIfVisible();

        // Update last activity in localStorage
        localStorage.setItem('lastActivity', Date.now().toString());

        // Start new timer
        startInactivityCheck();
    }

    /**
     * Start inactivity check
     */
    function startInactivityCheck() {
        // Don't start if not initialized or logout in progress
        if (!isInitialized || isLogoutInProgress) {
            return;
        }

        // Clear any existing timers first
        clearAllTimers();

        // Set logout timer
        logoutTimer = setTimeout(() => {
            if (!isLogoutInProgress && !warningShown) {
                performAutoLogout();
            }
        }, INACTIVITY_LIMIT * 60 * 1000);

        // Set warning timer
        warningTimer = setTimeout(() => {
            if (!isInitialized || isLogoutInProgress) {
                return;
            }
            if (!warningShown) {
                showWarningDialog();
            }
        }, (INACTIVITY_LIMIT - WARNING_TIME) * 60 * 1000);
    }

    /**
     * Show warning dialog before auto-logout
     */
    function showWarningDialog() {
        // Prevent multiple warning dialogs
        if (warningShown) {
            return;
        }

        warningShown = true;
        timeRemaining = WARNING_TIME * 60; // seconds

        // Stop the logout timer since we're showing the warning
        if (logoutTimer) {
            clearTimeout(logoutTimer);
            logoutTimer = null;
        }

        // Stop the warning timer since we're showing the warning now
        if (warningTimer) {
            clearTimeout(warningTimer);
            warningTimer = null;
        }

        // Show countdown modal
        Swal.fire({
            title: 'Peringatan Inaktivitas',
            html: `
                <div class="text-center">
                    <p class="text-gray-600 mb-4">
                        Anda tidak memiliki aktivitas selama beberapa waktu.
                    </p>
                    <p class="text-gray-700 mb-2">
                        Anda akan otomatis logout dalam:
                    </p>
                    <div class="text-4xl font-bold text-red-600 mb-4" id="countdown">
                        ${formatTime(timeRemaining)}
                    </div>
                    <p class="text-sm text-gray-500">
                        Klik tombol di bawah untuk melanjutkan sesi
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Lanjutkan Sesi',
            cancelButtonText: 'Logout Sekarang',
            confirmButtonColor: '#0052CC',
            cancelButtonColor: '#ef4444',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showClass: {
                popup: 'swal2-show',
                backdrop: 'swal2-backdrop-show',
                icon: 'swal2-icon-show'
            },
            hideClass: {
                popup: 'swal2-hide',
                backdrop: 'swal2-backdrop-hide',
                icon: 'swal2-icon-hide'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // User clicked "Lanjutkan Sesi"
                handleContinueSession();
            } else if (result.isDismissed || result.isDenied) {
                // User clicked "Logout Sekarang" or dismissed
                handleLogoutNow();
            }
        });

        // Start countdown
        countdownTimer = setInterval(() => {
            // Check if logout is in progress
            if (isLogoutInProgress) {
                clearInterval(countdownTimer);
                return;
            }

            timeRemaining--;
            const countdownElement = document.getElementById('countdown');
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

    /**
     * Handle "Lanjutkan Sesi" button click
     */
    function handleContinueSession() {
        // Clear countdown timer
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }

        // Reset warning state
        warningShown = false;
        timeRemaining = 0;

        // Update last activity
        localStorage.setItem('lastActivity', Date.now().toString());

        // Close current dialog and show success
        closeSwalIfVisible();

        Swal.fire({
            icon: 'success',
            title: 'Sesi Dilanjutkan',
            text: 'Sesi Anda telah diperpanjang. Silakan lanjutkan aktivitas Anda.',
            timer: 2000,
            showConfirmButton: false,
            timerProgressBar: true,
            didClose: () => {
                // Restart inactivity check after dialog closes
                startInactivityCheck();
            }
        });
    }

    /**
     * Handle "Logout Sekarang" button click
     */
    function handleLogoutNow() {
        // Clear countdown timer
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }

        // Reset warning state
        warningShown = false;
        timeRemaining = 0;

        // Close dialog and perform logout
        closeSwalIfVisible();
        performLogout();
    }

    /**
     * Format time as MM:SS
     */
    function formatTime(seconds) {
        const mins = Math.floor(Math.max(0, seconds) / 60);
        const secs = Math.max(0, seconds) % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    /**
     * Perform auto-logout
     */
    function performAutoLogout() {
        // Prevent multiple logout attempts
        if (isLogoutInProgress) {
            return;
        }

        isLogoutInProgress = true;

        // Clear all timers
        clearAllTimers();

        // Close any open dialogs
        closeSwalIfVisible();

        // Show logout notification with countdown
        Swal.fire({
            title: 'Sesi Berakhir',
            html: `
                <div class="text-center">
                    <p class="text-gray-600 mb-4">
                        Anda telah logout secara otomatis karena tidak ada aktivitas selama 10 menit.
                    </p>
                    <p class="text-sm text-gray-500">
                        Mengalihkan ke halaman login dalam:
                    </p>
                    <div class="text-3xl font-bold text-blue-600 mt-2" id="logout-countdown">
                        03
                    </div>
                </div>
            `,
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => {
                // Start countdown for redirect
                let redirectTime = 3;
                const countdownEl = document.getElementById('logout-countdown');
                const timerInterval = setInterval(() => {
                    redirectTime--;
                    if (countdownEl) {
                        countdownEl.textContent = redirectTime.toString().padStart(2, '0');
                    }
                    if (redirectTime <= 0) {
                        clearInterval(timerInterval);
                    }
                }, 1000);
            },
            willClose: () => {
                performLogout();
            }
        });

        // Fallback: also set a timeout to ensure logout happens
        setTimeout(() => {
            performLogout();
        }, 3500);
    }

    /**
     * Perform logout
     */
    function performLogout() {
        // Prevent multiple logout attempts
        if (isLogoutInProgress) {
            return;
        }

        isLogoutInProgress = true;

        // Clear all timers
        clearAllTimers();

        // Mark as logged out in localStorage to prevent re-initialization
        localStorage.setItem('autoLogoutExecuted', 'true');

        // Clear all session data
        localStorage.removeItem('lastActivity');
        localStorage.removeItem('sessionStartTime');
        localStorage.removeItem('autoLogoutExecuted');

        sessionStorage.clear();

        // Clear all cookies
        document.cookie.split(";").forEach((c) => {
            document.cookie = c
                .replace(/^ +/, "")
                .replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
        });

        // Clear cache
        if ('caches' in window) {
            caches.keys().then((names) => {
                names.forEach((name) => caches.delete(name));
            });
        }

        // Mark as not initialized
        isInitialized = false;

        // Redirect to logout
        window.location.href = '/logout';
    }

    /**
     * Initialize auto-logout system
     */
    function initAutoLogout() {
        // Prevent double initialization
        if (isInitialized) {
            return;
        }

        // Check if logout was already executed (prevent re-initialization after redirect)
        if (localStorage.getItem('autoLogoutExecuted') === 'true') {
            localStorage.removeItem('autoLogoutExecuted');
            return;
        }

        // Check if user is logged in
        const isLoggedIn = document.body.querySelector('[data-user-authenticated="true"]') ||
                          document.querySelector('meta[name="user-authenticated"]')?.content === 'true';

        if (!isLoggedIn) {
            return; // Don't initialize if not logged in
        }

        // Mark as initialized
        isInitialized = true;
        isLogoutInProgress = false;

        // Initialize last activity - ALWAYS reset on page load for fresh start
        localStorage.setItem('lastActivity', Date.now().toString());
        localStorage.setItem('sessionStartTime', Date.now().toString());

        // Monitor user activity
        const activityEvents = [
            'mousedown',
            'mousemove',
            'keypress',
            'scroll',
            'touchstart',
            'click'
        ];

        activityEvents.forEach(event => {
            document.addEventListener(event, resetInactivityTimer, { passive: true });
        });

        // Also monitor visibility change
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && isInitialized && !isLogoutInProgress) {
                // Page became visible again, check inactivity
                const lastActivity = localStorage.getItem('lastActivity');
                if (lastActivity) {
                    const inactiveMinutes = (Date.now() - parseInt(lastActivity)) / 1000 / 60;
                    if (inactiveMinutes >= INACTIVITY_LIMIT) {
                        performAutoLogout();
                    } else {
                        // Reset timer when page becomes visible
                        resetInactivityTimer();
                    }
                }
            }
        });

        // Start initial timer
        startInactivityCheck();

        // Expose function for sidebar logout button to call
        window.resumeAutoLogoutReset = function() {
            if (isInitialized && !isLogoutInProgress) {
                resetInactivityTimer();
            }
        };

        // Console log for debugging (remove in production)
        console.log('Auto-logout system initialized: ' + INACTIVITY_LIMIT + ' minutes');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAutoLogout);
    } else {
        initAutoLogout();
    }

})();
