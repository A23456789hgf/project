/**
 * Page Loader - Global Loading Indicator
 * Displays a loading overlay during page loads and navigation
 * Prevents user interaction until the page is fully loaded
 */

(function () {
    'use strict';

    // Configuration
    const config = {
        minDisplayTime: 0,  // Minimum time to show loader (ms) — set to 0 for instant feel
        fadeOutDuration: 150, // Must match CSS transition duration
        showOnNavigation: true,
        showOnFormSubmit: true,
        excludeSelectors: [
            '.no-loader',
            '[target="_blank"]',
            '[href^="#"]',
            '[data-bs-toggle]',
            '[data-toggle]'
        ]
    };

    let loaderStartTime = null;
    let isLoaderVisible = false;
    let loaderTimeout = null;
    let activeAjaxCount = 0;

    // Scroll Prevention System - Blocks scrolling without hiding scrollbar to prevent layout shifts
    const scrollKeys = { 32: 1, 33: 1, 34: 1, 35: 1, 36: 1, 37: 1, 38: 1, 39: 1, 40: 1 };

    function preventDefault(e) {
        e.preventDefault();
    }

    function preventDefaultForScrollKeys(e) {
        if (scrollKeys[e.keyCode]) {
            e.preventDefault();
            return false;
        }
    }

    function disableScroll() {
        window.addEventListener('wheel', preventDefault, { passive: false });
        window.addEventListener('touchmove', preventDefault, { passive: false });
        window.addEventListener('keydown', preventDefaultForScrollKeys, { passive: false });
    }

    function enableScroll() {
        window.removeEventListener('wheel', preventDefault, { passive: false });
        window.removeEventListener('touchmove', preventDefault, { passive: false });
        window.removeEventListener('keydown', preventDefaultForScrollKeys, { passive: false });
    }

    /**
     * Determine if a request URL or its headers should bypass the page loader
     */
    function isExcludedRequest(url, headers = {}) {
        if (!url) return false;

        // Convert to string in case it's a URL object
        const urlStr = String(url).toLowerCase();

        // Exclude notification endpoints
        if (urlStr.indexOf('notifications/unread-count') !== -1 ||
            urlStr.indexOf('notifications/latest') !== -1 ||
            urlStr.indexOf('notifications/mark-all-read') !== -1) {
            return true;
        }

        // Exclude background request header if present
        if (headers['X-Background-Request'] === 'true' || headers['x-background-request'] === 'true') {
            return true;
        }

        return false;
    }

    function incrementAjaxCount() {
        if (activeAjaxCount === 0) {
            // Delay showing loader by 250ms — fast requests won't flash the loader
            showPageLoader('جاري جلب البيانات...', 250);
        }
        activeAjaxCount++;
    }

    function decrementAjaxCount() {
        activeAjaxCount = Math.max(0, activeAjaxCount - 1);
        if (activeAjaxCount === 0) {
            hidePageLoader();
        }
    }

    /**
     * Show the page loader with debouncing to prevent flash on fast loads
     */
    function showPageLoader(message = 'جاري تحميل الصفحة...', delay = 0) {
        // Clear any existing timeout
        if (loaderTimeout) {
            clearTimeout(loaderTimeout);
        }

        const runShow = () => {
            const loader = document.getElementById('pageLoader');
            if (!loader || isLoaderVisible) return;

            const loaderText = loader.querySelector('.loader-text');
            if (loaderText) {
                loaderText.textContent = message;
            }

            loader.classList.add('active');
            disableScroll(); // Prevent scrolling without layout shift
            isLoaderVisible = true;
            loaderStartTime = Date.now();

            console.log('🔄 Page loader shown');
        };

        if (delay === 0) {
            runShow();
        } else {
            loaderTimeout = setTimeout(runShow, delay);
        }
    }

    function hidePageLoader() {
        if (loaderTimeout) {
            clearTimeout(loaderTimeout);
            loaderTimeout = null;
        }

        const loader = document.getElementById('pageLoader');
        if (!loader || !isLoaderVisible) return;

        const elapsed = Date.now() - loaderStartTime;
        const remaining = Math.max(0, config.minDisplayTime - elapsed);

        const doHide = () => {
            loader.classList.remove('active');
            enableScroll();
            isLoaderVisible = false;
        };

        if (remaining === 0) {
            doHide();
        } else {
            setTimeout(doHide, remaining);
        }
    }

    /**
     * Check if an element should trigger the loader
     */
    function shouldShowLoader(element) {
        // Check if element matches any exclude selector
        for (const selector of config.excludeSelectors) {
            if (element.matches(selector)) {
                return false;
            }
        }

        // Check if element or any parent has no-loader class
        if (element.closest('.no-loader')) {
            return false;
        }

        return true;
    }

    /**
     * Handle link clicks
     */
    function handleLinkClick(event) {
        if (!config.showOnNavigation) return;

        const link = event.target.closest('a');
        if (!link) return;

        // Check if we should show loader for this link
        if (shouldShowLoader(link)) {
            const href = link.getAttribute('href');

            // Only show loader for actual navigation (not javascript:void, etc.)
            if (href && !href.startsWith('javascript:') && !href.startsWith('mailto:') && !href.startsWith('tel:')) {
                showPageLoader('جاري الانتقال...');
            }
        }
    }

    /**
     * Handle form submissions
     */
    function handleFormSubmit(event) {
        if (!config.showOnFormSubmit) return;

        const form = event.target;

        // Check if we should show loader for this form
        if (shouldShowLoader(form)) {
            // Don't show loader if form validation failed
            if (form.checkValidity && !form.checkValidity()) {
                return;
            }

            // Check if it's an AJAX form (has data-ajax attribute)
            if (form.hasAttribute('data-ajax')) {
                return; // AJAX forms handle their own loading states
            }

            const submitButton = form.querySelector('[type="submit"]');
            const message = submitButton?.dataset?.loadingMessage || 'جاري معالجة البيانات...';

            showPageLoader(message);
        }
    }

    /**
     * Handle page visibility changes (for back/forward navigation)
     */
    function handleVisibilityChange() {
        if (document.visibilityState === 'visible') {
            // Page became visible again, hide loader
            hidePageLoader();
        }
    }

    /**
     * Handle browser back/forward buttons
     */
    function handlePageShow(event) {
        // If page is loaded from cache (bfcache), hide the loader
        if (event.persisted) {
            hidePageLoader();
        }
    }

    /**
     * Initialize the page loader system
     */
    function init() {
        // DON'T show loader on initial page load - only on navigation
        // The loader will show automatically when user clicks links or submits forms

        // Immediately hide any existing loader since the DOM is already ready
        // (We don't wait for window.onload to avoid waiting for heavy external assets like fonts/images)
        hidePageLoader();

        // Failsafe: Force hide loader after 8 seconds if stuck
        setTimeout(() => {
            if (isLoaderVisible) {
                const loader = document.getElementById('pageLoader');
                if (loader) {
                    loader.classList.remove('active');
                    enableScroll();
                    isLoaderVisible = false;
                }
            }
        }, 8000);

        // Handle navigation clicks
        document.addEventListener('click', handleLinkClick, true);

        // Handle form submissions
        document.addEventListener('submit', handleFormSubmit, true);

        // Handle visibility changes
        document.addEventListener('visibilitychange', handleVisibilityChange);

        // Handle back/forward navigation
        window.addEventListener('pageshow', handlePageShow);

        // 1. Monkey-patch HTMLFormElement.prototype.submit for programmatic submissions
        const originalSubmit = HTMLFormElement.prototype.submit;
        HTMLFormElement.prototype.submit = function () {
            if (shouldShowLoader(this)) {
                // If it is an AJAX form, don't show loader (or let AJAX interceptor handle it)
                if (!this.hasAttribute('data-ajax')) {
                    const submitButton = this.querySelector('[type="submit"]');
                    const message = submitButton?.dataset?.loadingMessage || 'جاري معالجة البيانات...';
                    showPageLoader(message, 0);
                }
            }
            return originalSubmit.apply(this, arguments);
        };
        console.log('✅ Page Loader initialized for navigation and form submissions');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose global functions for manual control
    window.showPageLoader = showPageLoader;
    window.hidePageLoader = hidePageLoader;

    // Backward compatibility aliases for legacy scripts
    window.showLoader = showPageLoader;
    window.hideLoader = hidePageLoader;

})();