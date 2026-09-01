/**
 * Responsive Behavior Handler
 * Dynamically adjusts page elements based on screen size
 */

(function () {
    'use strict';

    // Configuration
    const breakpoints = {
        xs: 576,
        sm: 768,
        md: 992,
        lg: 1200,
        xl: 1400
    };

    let currentBreakpoint = getCurrentBreakpoint();
    let resizeTimer;

    /**
     * Get current breakpoint
     */
    function getCurrentBreakpoint() {
        const width = window.innerWidth;
        if (width < breakpoints.xs) return 'xs';
        if (width < breakpoints.sm) return 'sm';
        if (width < breakpoints.md) return 'md';
        if (width < breakpoints.lg) return 'lg';
        if (width < breakpoints.xl) return 'xl';
        return 'xxl';
    }

    /**
     * Handle table responsiveness
     */
    function handleTableResponsiveness() {
        const tables = document.querySelectorAll('table:not(.table-responsive table)');

        tables.forEach(table => {
            // Wrap table if not already wrapped
            if (!table.parentElement.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    }

    /**
     * Handle long text truncation on mobile
     */
    function handleTextTruncation() {
        if (window.innerWidth < breakpoints.sm) {
            const longTexts = document.querySelectorAll('.truncate-mobile');
            longTexts.forEach(el => {
                const originalText = el.dataset.originalText || el.textContent;
                el.dataset.originalText = originalText;

                if (originalText.length > 50) {
                    el.textContent = originalText.substring(0, 47) + '...';
                    el.title = originalText;
                }
            });
        } else {
            const truncated = document.querySelectorAll('.truncate-mobile[data-original-text]');
            truncated.forEach(el => {
                el.textContent = el.dataset.originalText;
            });
        }
    }

    /**
     * Handle button text on mobile
     */
    function handleButtonText() {
        const buttons = document.querySelectorAll('.btn[data-mobile-text]');

        buttons.forEach(btn => {
            const desktopText = btn.dataset.desktopText || btn.textContent.trim();
            const mobileText = btn.dataset.mobileText;

            if (!btn.dataset.desktopText) {
                btn.dataset.desktopText = desktopText;
            }

            if (window.innerWidth < breakpoints.sm && mobileText) {
                btn.innerHTML = mobileText;
            } else {
                btn.innerHTML = desktopText;
            }
        });
    }

    /**
     * Handle card stacking on mobile
     */
    function handleCardStacking() {
        const cardGroups = document.querySelectorAll('.card-group, .row');

        cardGroups.forEach(group => {
            if (window.innerWidth < breakpoints.md) {
                group.classList.add('flex-column');
            } else {
                group.classList.remove('flex-column');
            }
        });
    }

    /**
     * Handle form layout on mobile
     */
    function handleFormLayout() {
        const formRows = document.querySelectorAll('.form-row, .row');

        formRows.forEach(row => {
            const cols = row.querySelectorAll('[class*="col-"]');

            if (window.innerWidth < breakpoints.sm) {
                cols.forEach(col => {
                    col.style.marginBottom = '1rem';
                });
            } else {
                cols.forEach(col => {
                    col.style.marginBottom = '';
                });
            }
        });
    }

    /**
     * Handle sidebar responsiveness
     */
    function handleSidebarResponsiveness() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');

        if (!sidebar || !mainContent) return;

        if (window.innerWidth < breakpoints.md) {
            // Mobile: sidebar should be collapsed by default
            if (!sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
            }
        }
    }

    /**
     * Handle modal responsiveness
     */
    function handleModalResponsiveness() {
        const modals = document.querySelectorAll('.modal-dialog');

        modals.forEach(modal => {
            if (window.innerWidth < breakpoints.sm) {
                modal.classList.add('modal-fullscreen-sm-down');
            } else {
                modal.classList.remove('modal-fullscreen-sm-down');
            }
        });
    }

    /**
     * Handle dropdown positioning
     */
    function handleDropdownPositioning() {
        const dropdowns = document.querySelectorAll('.dropdown-menu');

        dropdowns.forEach(dropdown => {
            if (window.innerWidth < breakpoints.sm) {
                dropdown.style.maxWidth = '90vw';
            } else {
                dropdown.style.maxWidth = '';
            }
        });
    }

    /**
     * Add responsive classes to body
     */
    function updateBodyClasses() {
        const body = document.body;
        const bp = getCurrentBreakpoint();

        // Remove all breakpoint classes
        body.classList.remove('is-xs', 'is-sm', 'is-md', 'is-lg', 'is-xl', 'is-xxl');

        // Add current breakpoint class
        body.classList.add(`is-${bp}`);

        // Add mobile/tablet/desktop classes
        if (bp === 'xs' || bp === 'sm') {
            body.classList.add('is-mobile');
            body.classList.remove('is-tablet', 'is-desktop');
        } else if (bp === 'md') {
            body.classList.add('is-tablet');
            body.classList.remove('is-mobile', 'is-desktop');
        } else {
            body.classList.add('is-desktop');
            body.classList.remove('is-mobile', 'is-tablet');
        }
    }

    /**
     * Handle viewport height for mobile browsers
     */
    function handleViewportHeight() {
        // Fix for mobile browsers where 100vh includes address bar
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }

    /**
     * Handle orientation change
     */
    function handleOrientationChange() {
        const isPortrait = window.innerHeight > window.innerWidth;
        document.body.classList.toggle('is-portrait', isPortrait);
        document.body.classList.toggle('is-landscape', !isPortrait);
    }

    /**
     * Prevent zoom on double tap (iOS)
     */
    function preventDoubleTapZoom() {
        let lastTouchEnd = 0;

        document.addEventListener('touchend', function (event) {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, { passive: false });
    }

    /**
     * Handle all responsive behaviors
     */
    function handleResponsiveBehaviors() {
        handleTableResponsiveness();
        handleTextTruncation();
        handleButtonText();
        handleCardStacking();
        handleFormLayout();
        handleSidebarResponsiveness();
        handleModalResponsiveness();
        handleDropdownPositioning();
        updateBodyClasses();
        handleViewportHeight();
        handleOrientationChange();
    }

    /**
     * Debounced resize handler
     */
    function onResize() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const newBreakpoint = getCurrentBreakpoint();

            // Only run if breakpoint changed
            if (newBreakpoint !== currentBreakpoint) {
                currentBreakpoint = newBreakpoint;
                handleResponsiveBehaviors();

                // Dispatch custom event
                window.dispatchEvent(new CustomEvent('breakpointChange', {
                    detail: { breakpoint: newBreakpoint }
                }));
            }

            // Always update viewport height
            handleViewportHeight();
        }, 150);
    }

    /**
     * Initialize responsive behaviors
     */
    function init() {
        // Initial setup
        handleResponsiveBehaviors();

        // Prevent double tap zoom on iOS
        if (/iPhone|iPad|iPod/.test(navigator.userAgent)) {
            preventDoubleTapZoom();
        }

        // Listen for resize
        window.addEventListener('resize', onResize);

        // Listen for orientation change
        window.addEventListener('orientationchange', () => {
            setTimeout(handleResponsiveBehaviors, 100);
        });

        // Re-check on DOM changes (for dynamically added content)
        const observer = new MutationObserver(() => {
            handleTableResponsiveness();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        console.log('✅ Responsive system initialized');
        console.log(`📱 Current breakpoint: ${currentBreakpoint}`);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose utility functions globally
    window.ResponsiveUtils = {
        getCurrentBreakpoint,
        isMobile: () => window.innerWidth < breakpoints.md,
        isTablet: () => window.innerWidth >= breakpoints.md && window.innerWidth < breakpoints.lg,
        isDesktop: () => window.innerWidth >= breakpoints.lg,
        breakpoints
    };

})();
