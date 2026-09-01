/**
 * Hierarchical Permissions Matrix Logic
 * Handles 3-level cascading visibility and selection with smooth animations.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const ELEMENTS = {
        globalSelectAll: document.getElementById('globalSelectAll'),
        globalDeselectAll: document.getElementById('globalDeselectAll'),
        fullAccessSwitch: document.getElementById('full_access'),
        permissionSearch: document.getElementById('permissionSearch'),
        selectedCount: document.getElementById('selectedCount'),
        collapseAll: document.getElementById('collapseAll'),
        expandAll: document.getElementById('expandAll'),
        containers: document.querySelectorAll('.permissions-container'),
        permCheckboxes: () => Array.from(document.querySelectorAll('.perm-checkbox')),
        moduleCheckboxes: () => Array.from(document.querySelectorAll('.module-check-all')),
        moduleRows: () => Array.from(document.querySelectorAll('tr.module-row'))
    };

    function updateSelectedCount() {
        const selected = ELEMENTS.permCheckboxes().filter(cb => cb.checked).length;
        if (ELEMENTS.selectedCount) {
            ELEMENTS.selectedCount.textContent = selected;
        }
    }

    /**
     * Toggles a level container with smooth animation
     */
    function toggleLevel(targetId, forceShow = false) {
        const target = document.getElementById(targetId);
        if (!target) return;

        const button = document.querySelector(`[data-target="${targetId}"]`);
        const icon = button?.querySelector('.ripple-icon');

        if (forceShow || target.classList.contains('d-none')) {
            target.classList.remove('d-none');
            // Animate height
            target.style.height = '0';
            target.style.opacity = '0';
            target.style.overflow = 'hidden';
            target.style.transition = 'height 0.3s ease, opacity 0.3s ease';

            const height = target.scrollHeight;
            target.style.height = height + 'px';
            target.style.opacity = '1';

            if (icon) icon.style.transform = 'rotate(180deg)';

            setTimeout(() => {
                target.style.height = '';
                target.style.overflow = '';
            }, 300);
        } else if (!forceShow) {
            target.style.height = target.scrollHeight + 'px';
            target.style.transition = 'height 0.3s ease, opacity 0.3s ease';
            target.style.opacity = '1';
            target.style.overflow = 'hidden';

            // Force reflow
            target.offsetHeight;

            target.style.height = '0';
            target.style.opacity = '0';

            if (icon) icon.style.transform = 'rotate(0deg)';

            setTimeout(() => {
                target.classList.add('d-none');
                target.style.height = '';
            }, 300);
        }
    }

    /**
     * Handles visibility of sub-levels based on parent state
     */
    function updateVisibility(checkbox) {
        const level = parseInt(checkbox.dataset.level);
        if (isNaN(level)) return;

        const container = checkbox.closest(`.level-${level}-container`);
        if (!container) return;

        const wrapper = container.querySelector(`.level-${level + 1}-wrapper`);
        if (wrapper) {
            if (checkbox.checked) {
                // We don't automatically show with animation here to avoid jumps when selecting
                // But we ensure it's not d-none if it's checked
                wrapper.classList.remove('d-none');
            } else {
                wrapper.classList.add('d-none');
                // Recursively uncheck children if hidden
                const children = wrapper.querySelectorAll('.perm-checkbox');
                children.forEach(child => {
                    if (child.checked) {
                        child.checked = false;
                        const card = child.closest('.permission-card');
                        if (card) card.classList.remove('selected');
                        updateVisibility(child);
                    }
                });
            }
        }
    }

    /**
     * Cascading Selection Logic
     */
    function handleHierarchy(checkbox) {
        const level = parseInt(checkbox.dataset.level);
        const row = checkbox.closest('tr');

        if (checkbox.checked) {
            // Check Parents (Upwards)
            if (level === 3) {
                const parentLevel2 = checkbox.closest('.level-2-container')?.querySelector('.level-2-check');
                if (parentLevel2 && !parentLevel2.checked) {
                    parentLevel2.checked = true;
                    parentLevel2.closest('.permission-card')?.classList.add('selected');
                    updateVisibility(parentLevel2);
                    handleHierarchy(parentLevel2);
                }
            } else if (level === 2) {
                const parentLevel1 = checkbox.closest('.level-1-container')?.querySelector('.level-1-check');
                if (parentLevel1 && !parentLevel1.checked) {
                    parentLevel1.checked = true;
                    parentLevel1.closest('.permission-card')?.classList.add('selected');
                    updateVisibility(parentLevel1);
                }
            }
        }

        updateVisibility(checkbox);
        updateModuleHeader(row);
    }

    function updateModuleHeader(row) {
        const moduleCheckbox = row.querySelector('.module-check-all');
        if (moduleCheckbox) {
            const checkboxes = Array.from(row.querySelectorAll('.perm-checkbox'));
            moduleCheckbox.checked = checkboxes.length > 0 && checkboxes.every(cb => cb.checked);
        }
    }

    function toggleFullAccess() {
        const isEnabled = ELEMENTS.fullAccessSwitch?.checked;
        const checkboxes = ELEMENTS.permCheckboxes();
        const moduleCheckboxes = ELEMENTS.moduleCheckboxes();

        checkboxes.forEach(cb => {
            cb.disabled = isEnabled;
            if (isEnabled) {
                cb.checked = true;
                cb.closest('.permission-card')?.classList.add('selected');
            }
        });

        document.querySelectorAll('[class*="-wrapper"]').forEach(w => {
            if (isEnabled) w.classList.remove('d-none');
        });

        moduleCheckboxes.forEach(cb => {
            cb.disabled = isEnabled;
            if (isEnabled) cb.checked = true;
        });

        [ELEMENTS.globalSelectAll, ELEMENTS.globalDeselectAll].forEach(btn => {
            if (btn) btn.disabled = isEnabled;
        });

        updateSelectedCount();
    }

    // --- EVENT LISTENERS ---

    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.toggle-level');
        if (toggleBtn) {
            toggleLevel(toggleBtn.dataset.target);
        }
    });

    ELEMENTS.globalSelectAll?.addEventListener('click', () => {
        if (ELEMENTS.fullAccessSwitch && !ELEMENTS.fullAccessSwitch.checked) {
            ELEMENTS.fullAccessSwitch.checked = true;
            toggleFullAccess();
        } else {
            ELEMENTS.permCheckboxes().forEach(cb => {
                cb.checked = true;
                cb.closest('.permission-card')?.classList.add('selected');
                updateVisibility(cb);
            });
            ELEMENTS.moduleCheckboxes().forEach(cb => cb.checked = true);
        }
        updateSelectedCount();
    });

    ELEMENTS.globalDeselectAll?.addEventListener('click', () => {
        if (ELEMENTS.fullAccessSwitch && ELEMENTS.fullAccessSwitch.checked) {
            ELEMENTS.fullAccessSwitch.checked = false;
            toggleFullAccess();
        }
        ELEMENTS.permCheckboxes().forEach(cb => {
            cb.checked = false;
            cb.closest('.permission-card')?.classList.remove('selected');
            updateVisibility(cb);
        });
        ELEMENTS.moduleCheckboxes().forEach(cb => cb.checked = false);
        updateSelectedCount();
    });

    ELEMENTS.moduleCheckboxes().forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const row = this.closest('tr');
            const perms = Array.from(row.querySelectorAll('.perm-checkbox'));
            perms.forEach(cb => {
                cb.checked = this.checked;
                cb.closest('.permission-card')?.classList.toggle('selected', this.checked);
                updateVisibility(cb);
            });
            updateSelectedCount();
        });
    });

    ELEMENTS.permCheckboxes().forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const card = this.closest('.permission-card');
            if (card) card.classList.toggle('selected', this.checked);
            handleHierarchy(this);
            updateSelectedCount();
        });
    });

    document.querySelectorAll('.permission-card').forEach(card => {
        card.addEventListener('click', function (e) {
            if (e.target.closest('.permission-checkbox') || e.target.closest('.permission-slug') || e.target.closest('button')) return;
            const checkbox = this.querySelector('.perm-checkbox');
            if (checkbox && !checkbox.disabled) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });

    ELEMENTS.permissionSearch?.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();

        document.querySelectorAll('.permission-card').forEach(card => {
            const name = card.querySelector('.perm-name')?.textContent.toLowerCase() || '';
            const slug = card.querySelector('.permission-slug code')?.textContent.toLowerCase() || '';
            const isMatch = name.includes(query) || slug.includes(query);

            const itemContainer = card.closest('.level-1-container, .level-2-container, .col-12');
            if (itemContainer) {
                itemContainer.classList.toggle('d-none', !isMatch && query !== '');

                if (isMatch && query !== '') {
                    let parent = card.closest('[class*="-wrapper"]');
                    while (parent) {
                        if (parent.classList.contains('d-none')) {
                            toggleLevel(parent.id, true);
                        }
                        parent = parent.parentElement.closest('[class*="-wrapper"]');
                    }
                }
            }
        });

        ELEMENTS.moduleRows().forEach(row => {
            const hasVisible = row.querySelectorAll('.permission-card:not(.d-none)').length > 0;
            row.style.display = hasVisible || query === '' ? '' : 'none';
        });
    });

    ELEMENTS.collapseAll?.addEventListener('click', () => {
        ELEMENTS.containers.forEach(c => c.style.display = 'none');
    });
    ELEMENTS.expandAll?.addEventListener('click', () => {
        ELEMENTS.containers.forEach(c => c.style.display = '');
    });

    ELEMENTS.fullAccessSwitch?.addEventListener('change', toggleFullAccess);

    function init() {
        if (ELEMENTS.fullAccessSwitch?.checked) {
            toggleFullAccess();
        } else {
            ELEMENTS.moduleRows().forEach(row => updateModuleHeader(row));
            ELEMENTS.permCheckboxes().forEach(cb => {
                cb.closest('.permission-card')?.classList.toggle('selected', cb.checked);
                if (cb.checked) {
                    const level = parseInt(cb.dataset.level);
                    const wrapper = cb.closest(`.level-${level}-container`)?.querySelector(`.level-${level + 1}-wrapper`);
                    if (wrapper) wrapper.classList.remove('d-none');
                }
            });
        }
        updateSelectedCount();
    }

    init();
});
