/**
 * Global Searchable Select2 Dropdowns
 * =====================================
 * يُطبّق ميزة البحث على جميع قوائم الـ dropdown في النظام بشكل موحّد
 * مع دعم كامل للغة العربية وواجهة RTL وجميع أنواع القوائم المترابطة
 *
 * الاستخدام:
 *  - تلقائياً على جميع الـ <select> عند تحميل الصفحة
 *  - إضافة class="no-search" لاستثناء قائمة معينة
 *  - window.initGlobalSelect2(container, force) لتهيئة يدوية
 *  - window.getGlobalSelect2Config($element) للحصول على الإعدادات
 */
(function ($) {
    'use strict';

    /**
     * الحصول على إعدادات Select2 الموحّدة
     * @param {jQuery} $element - العنصر المراد تهيئته
     * @returns {Object} كائن الإعدادات
     */
    window.getGlobalSelect2Config = function ($element) {
        // تحديد الحاوي الأب (مودال إن وُجد)
        var $modal = $element ? $element.closest('.modal') : $();
        var parent = $modal.length > 0 ? $modal : $(document.body);

        // الحفاظ على الكلاسات الأصلية (باستثناء كلاسات select2 وform-select)
        var originalClasses = $element ? ($element.attr('class') || '') : '';
        var allowedClasses = originalClasses.split(/\s+/).filter(function (c) {
            return c &&
                c !== 'form-select' &&
                c !== 'form-control' &&
                c !== 'select2-hidden-accessible' &&
                !/^select2/.test(c);
        });
        var selectionCssClass = allowedClasses.join(' ');

        // كلاسات الـ dropdown بدون كلاسات الحجم (sm/lg) لتجنب تقييد الارتفاع
        var dropdownCssClass = allowedClasses.filter(function (c) {
            return c !== 'form-select-sm' && c !== 'form-control-sm' &&
                   c !== 'form-select-lg' && c !== 'form-control-lg';
        }).join(' ');

        var config = {
            theme: 'bootstrap-5',
            dir: 'rtl',
            width: '100%',
            dropdownParent: parent,
            selectionCssClass: selectionCssClass || ':keep',
            dropdownCssClass: dropdownCssClass,
            language: {
                noResults: function () {
                    return 'لا توجد نتائج مطابقة';
                },
                searching: function () {
                    return 'جارٍ البحث...';
                },
                inputTooShort: function () {
                    return 'يرجى كتابة حرف واحد على الأقل للبحث';
                },
                inputTooLong: function (args) {
                    return 'يرجى حذف ' + (args.input.length - args.maximum) + ' حرف';
                },
                maximumSelected: function (args) {
                    return 'يمكنك اختيار ' + args.maximum + ' عناصر فقط';
                },
                loadingMore: function () {
                    return 'جارٍ تحميل المزيد...';
                }
            }
        };

        // دعم placeholder وزر المسح
        if ($element) {
            var placeholder = $element.attr('placeholder') || $element.data('placeholder');
            if (placeholder) {
                config.placeholder = placeholder;
                config.allowClear = true;
            }
        }

        // دعم AJAX للقوائم الديناميكية
        if ($element && $element.data('ajax-url')) {
            config.ajax = {
                url: $element.data('ajax-url'),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    var query = {
                        q: params.term || '',
                        type: $element.data('ajax-type') || '',
                        page: params.page || 1
                    };

                    // معاملات ديناميكية إضافية: data-ajax-params="parent_id=#select_id"
                    var extraParams = $element.data('ajax-params');
                    if (extraParams) {
                        try {
                            String(extraParams).split(',').forEach(function (item) {
                                var parts = item.split('=');
                                if (parts.length === 2) {
                                    var key = parts[0].trim();
                                    var selector = parts[1].trim();
                                    if (selector.indexOf('|') !== -1) {
                                        var sp = selector.split('|');
                                        var container = sp[0].replace('closest:', '').trim();
                                        var target = sp[1].trim();
                                        query[key] = $element.closest(container).find(target).val();
                                    } else {
                                        query[key] = $(selector).val();
                                    }
                                }
                            });
                        } catch (e) {
                            console.warn('[Select2] خطأ في تحليل ajax-params:', e);
                        }
                    }
                    return query;
                },
                processResults: function (data) {
                    return {
                        results: data.results || [],
                        pagination: {
                            more: data.pagination ? data.pagination.more : false
                        }
                    };
                },
                cache: false
            };
            config.minimumInputLength = 0;
        }

        return config;
    };

    /**
     * تهيئة Select2 على الحاوي المحدد
     * @param {jQuery|HTMLElement|String|null} container - الحاوي (null = كامل الصفحة)
     * @param {Boolean} force - إعادة التهيئة حتى لو مُهيَّأة مسبقاً
     */
    window.initGlobalSelect2 = function (container, force) {
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
            console.warn('[Select2] jQuery أو Select2 غير محمّل بعد');
            return;
        }

        var $container = container ? $(container) : $('body');

        // إذا كان الحاوي نفسه هو عنصر select
        if ($container.is('select')) {
            _initSingleSelect($container, force);
            return;
        }

        var selector = force
            ? 'select:not(.no-search)'
            : 'select:not(.no-search):not(.select2-hidden-accessible)';

        $container.find(selector).each(function () {
            _initSingleSelect($(this), force);
        });
    };

    /**
     * تهيئة عنصر select واحد
     * @private
     */
    function _initSingleSelect($select, force) {
        if (force && $select.hasClass('select2-hidden-accessible')) {
            try {
                $select.select2('destroy');
            } catch (e) { /* تجاهل أخطاء الـ destroy */ }
        }

        if (!$select.hasClass('select2-hidden-accessible')) {
            try {
                $select.select2(window.getGlobalSelect2Config($select));
            } catch (e) {
                console.warn('[Select2] فشل تهيئة العنصر:', $select.attr('id') || $select.attr('name'), e);
            }
        }
    }

    /**
     * مُراقب للعناصر الديناميكية (Dynamic DOM Observer)
     * يرصد إضافة عناصر <select> جديدة إلى DOM ويُهيّئها تلقائياً
     */
    function _observeDynamicSelects() {
        if (typeof MutationObserver === 'undefined') { return; }

        var observer = new MutationObserver(function (mutations) {
            var pending = [];
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) { return; } // عناصر DOM فقط
                    // العنصر نفسه إن كان select
                    if (node.tagName === 'SELECT' && !node.classList.contains('no-search')) {
                        pending.push(node);
                    }
                    // أو البحث داخله عن selects
                    var nested = node.querySelectorAll
                        ? node.querySelectorAll('select:not(.no-search)')
                        : [];
                    nested.forEach(function (el) { pending.push(el); });
                });
            });

            if (pending.length > 0) {
                // تأخير قصير للتأكد من اكتمال الـ render
                setTimeout(function () {
                    pending.forEach(function (el) {
                        var $el = $(el);
                        if (!$el.hasClass('select2-hidden-accessible')) {
                            _initSingleSelect($el, false);
                        }
                    });
                }, 50);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // ===== التهيئة عند تحميل الصفحة =====
    $(function () {
        // تهيئة جميع الـ selects
        window.initGlobalSelect2();

        // تهيئة عند فتح أي مودال
        $(document).on('shown.bs.modal', function (e) {
            window.initGlobalSelect2(e.target, false);
        });

        // إعادة تهيئة بعد إغلاق وإعادة فتح المودال (في حالة تحديث المحتوى)
        $(document).on('hidden.bs.modal', function (e) {
            $(e.target).find('select.select2-hidden-accessible').each(function () {
                try { $(this).select2('destroy'); } catch (e2) { /* تجاهل */ }
            });
        });

        // مراقبة العناصر الديناميكية
        _observeDynamicSelects();

        console.log('[Select2] ✅ تم تهيئة قوائم البحث على جميع الـ Dropdowns');
    });

})(jQuery);
