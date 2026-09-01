/**
 * Global Select2 Initialization
 * Applies searchable dropdowns to all select elements
 */
(function ($) {
    'use strict';

    /**
     * Get the standard Select2 configuration
     * @param {jQuery} $element - The element to get config for (for modal parent check)
     * @returns {Object} Select2 configuration object
     */
    window.getGlobalSelect2Config = function ($element) {
        var parent = $element ? $element.closest('.modal') : [];
        
        // Extract original classes to preserve formatting
        var originalClasses = $element ? ($element.attr('class') || '') : '';
        // Remove structural classes that might conflict with Select2 rendering, but keep sizing and borders
        // Use split and filter to avoid partial matches on hyphenated classes
        var classesArray = originalClasses.split(/\s+/);
        var cssClassesToPreserve = classesArray.filter(function(c) {
            return c !== 'form-select' && c !== 'form-control' && c !== 'select2-hidden-accessible';
        }).join(' ');
        
        // Exclude size classes for dropdown so its height is not restricted to 36px
        var dropdownClassesToPreserve = classesArray.filter(function(c) {
            return c !== 'form-select' && c !== 'form-control' && c !== 'select2-hidden-accessible' && c !== 'form-select-sm' && c !== 'form-control-sm' && c !== 'form-select-lg' && c !== 'form-control-lg';
        }).join(' ');
        
        var config = {
            theme: 'bootstrap-5',
            dir: "rtl",
            width: '100%',
            selectionCssClass: cssClassesToPreserve,
            dropdownCssClass: dropdownClassesToPreserve,
            language: {
                noResults: function () {
                    return "لا توجد نتائج";
                },
                searching: function () {
                    return "جاري البحث...";
                },
                inputTooShort: function (args) {
                    return "يرجى إدخال حرف واحد أو أكثر";
                },
                loadingMore: function () {
                    return "جاري تحميل المزيد...";
                }
            }
        };

        if (parent.length > 0) {
            config.dropdownParent = parent;
        }

        if ($element && $element.attr('placeholder')) {
            config.placeholder = $element.attr('placeholder');
            config.allowClear = true;
        }

        // AJAX Support for Searchable Dropdowns
        if ($element && $element.data('ajax-url')) {
            config.ajax = {
                url: $element.data('ajax-url'),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    var query = {
                        q: params.term,
                        type: $element.attr('data-ajax-type') || $element.data('ajax-type'),
                        page: params.page || 1
                    };

                    // Add dynamic parameters if specified
                    // Format: data-ajax-params="parent_id=#parent_select_id"
                    var extraParams = $element.data('ajax-params');
                    if (extraParams) {
                        try {
                            extraParams.split(',').forEach(function (item) {
                                var parts = item.split('=');
                                if (parts.length === 2) {
                                    var key = parts[0].trim();
                                    var selector = parts[1].trim();

                                    if (selector.indexOf('|') !== -1) {
                                        var selectorParts = selector.split('|');
                                        var container = selectorParts[0].replace('closest:', '').trim();
                                        var target = selectorParts[1].trim();
                                        query[key] = $element.closest(container).find(target).val();
                                    } else {
                                        query[key] = $(selector).val();
                                    }
                                }
                            });
                        } catch (e) {
                            console.error('Error parsing ajax-params:', e);
                        }
                    }

                    return query;
                },
                processResults: function (data, params) {
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination ? data.pagination.more : false
                        }
                    };
                },
                cache: false
            };

            // If it has initial data, don't require input to show them
            config.minimumInputLength = 0;
        }

        return config;
    };

    /**
     * Function to initialize Select2
     * @param {jQuery|HTMLElement|String} container - Optional container to scope the search
     * @param {Boolean} force - If true, re-initialize even if already initialized
     */
    window.initGlobalSelect2 = function (container, force) {
        const $container = container ? $(container) : $('body');

        // If the container itself is a select, use it directly
        if ($container.is('select')) {
            if (force && $container.hasClass('select2-hidden-accessible')) {
                $container.select2('destroy');
            }
            if (force || !$container.hasClass('select2-hidden-accessible')) {
                $container.select2(window.getGlobalSelect2Config($container));
            }
            return;
        }

        const selector = force ? 'select:not(.no-search)' : 'select:not(.no-search):not(.select2-hidden-accessible)';

        $container.find(selector).each(function () {
            var $this = $(this);
            if (force && $this.hasClass('select2-hidden-accessible')) {
                $this.select2('destroy');
            }
            $this.select2(window.getGlobalSelect2Config($this));
        });
    };

    $(function () {
        // Initialize on load
        window.initGlobalSelect2();

        // Re-initialize when a modal is opened (in case of dynamic content)
        $(document).on('shown.bs.modal', function () {
            window.initGlobalSelect2(this);
        });
    });
})(jQuery);
