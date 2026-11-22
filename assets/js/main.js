/**
 * Main Porsche Store Global Scripts
 */

(function () {
    'use strict';

    // Global Toast Notification
    window.toast = function (message) {
        var toastBox = document.getElementById('toast');
        if (!toastBox) return;

        toastBox.textContent = message;
        toastBox.style.display = 'block';

        if (window.toastTimeout) clearTimeout(window.toastTimeout);
        window.toastTimeout = setTimeout(function () {
            toastBox.style.display = 'none';
        }, 2800);
    };

    // Welcome box auto-dismiss
    function handleWelcomeBox() {
        var welcomeBox = document.getElementById('welcomeBox');
        if (welcomeBox) {
            setTimeout(function () {
                welcomeBox.style.opacity = '0';
                welcomeBox.style.transform = 'translate(-50%, -20px)';
                welcomeBox.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                setTimeout(function () {
                    welcomeBox.style.display = 'none';
                }, 500);
            }, 3000);
        }
    }

    // Number Count-Up Animation (Admin KPIs & Metrics)
    function setupCountUps() {
        var counters = document.querySelectorAll('.count-up');
        counters.forEach(function (counter) {
            var target = Number(counter.dataset.number) || 0;
            var current = 0;
            var step = Math.max(1, Math.ceil(target / 25));

            var interval = setInterval(function () {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(interval);
                }
                counter.textContent = current.toLocaleString('en-US');
            }, 35);
        });
    }

    // Header scroll background elevation
    function setupHeaderScroll() {
        var header = document.getElementById('mainHeader');
        if (!header) return;

        window.addEventListener('scroll', function () {
            if (window.scrollY > 30) {
                header.style.boxShadow = '0 6px 24px rgba(0, 0, 0, 0.6)';
            } else {
                header.style.boxShadow = 'none';
            }
        });
    }

    // Initialize Global Behaviors
    handleWelcomeBox();
    setupCountUps();
    setupHeaderScroll();
})();
