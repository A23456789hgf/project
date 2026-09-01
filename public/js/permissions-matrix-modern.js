/**
 * Permissions Matrix Modern JS
 * Handles AJAX-based real-time permission updates and scope toggles
 * 
 * Supports:
 * - module_scopes (administrative scope)
 * - module_geo_scopes (geographic scope)
 * - entity_display_scope / entity_add_scope (for authorities/internal-entities)
 * - full_access toggle
 * - Bulk select/deselect
 * - Live search
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Permissions Matrix JS Initialized');

    const matrixContainer = document.getElementById('matrixContainer');
    if (!matrixContainer) return;

    // Toastr Configuration
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-left",
            "timeOut": "3000"
        };
    }

    /**
     * AJAX Helper for CSRF
     */
    async function apiRequest(url, data) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error occurred');
            }

            return await response.json();
        } catch (error) {
            console.error('API Request Failed:', error);
            throw error;
        }
    }

    /**
     * Update Permission (Toggle Checkbox)
     */
    async function togglePermission(checkbox) {
        const roleId = checkbox.dataset.roleId;
        const permissionId = checkbox.dataset.permissionId;
        const action = checkbox.checked ? 'attach' : 'detach';
        
        const label = checkbox.closest('.modern-perm-card');
        if (label) {
            label.style.opacity = '0.5';
            if (checkbox.checked) label.classList.add('is-checked');
            else label.classList.remove('is-checked');
        }

        try {
            const result = await apiRequest('/roles-permissions/toggle', {
                role_id: roleId,
                permission_id: permissionId,
                action: action
            });

            if (result.success) {
                if (typeof toastr !== 'undefined') toastr.success(result.message || 'تم التحديث');
            } else {
                checkbox.checked = !checkbox.checked;
                if (label) {
                    if (checkbox.checked) label.classList.add('is-checked');
                    else label.classList.remove('is-checked');
                }
                if (typeof toastr !== 'undefined') toastr.error(result.message || 'فشل التحديث');
            }
        } catch (error) {
            checkbox.checked = !checkbox.checked;
            if (label) {
                if (checkbox.checked) label.classList.add('is-checked');
                else label.classList.remove('is-checked');
            }
            if (typeof toastr !== 'undefined') toastr.error('حدث خطأ أثناء الاتصال بالخادم');
        } finally {
            if (label) label.style.opacity = '1';
        }
    }

    /**
     * Update Module Scope (Administrative or Geographic)
     * Handles both module_scopes and module_geo_scopes
     */
    async function updateModuleScope(select) {
        const roleId = select.dataset.roleId;
        const module = select.dataset.module;
        const field = select.dataset.field; // 'scope' or 'geo_scope'
        let value = select.value;

        // Determine the database field name
        const dbField = field === 'scope' ? 'module_scopes' : 'module_geo_scopes';

        // If geographic scope is 'custom', we might need to show a modal for custom IDs
        // For now, we just send the string 'custom' and the backend will handle.
        // In future, you can add a popup to select specific governorates.

        select.disabled = true;

        try {
            const result = await apiRequest('/roles-permissions/toggle-scope', {
                role_id: roleId,
                field: dbField,
                module: module,
                value: value
            });

            if (result.success) {
                if (typeof toastr !== 'undefined') toastr.success(result.message || 'تم تحديث النطاق');
                // If needed, refresh UI for custom scope selection
                if (value === 'custom') {
                    // Optionally trigger a modal for custom governorate selection
                    console.log('Custom geographic scope selected for module:', module);
                }
            } else {
                // Revert select to previous value on error
                const previousValue = select.getAttribute('data-previous-value');
                if (previousValue) select.value = previousValue;
                if (typeof toastr !== 'undefined') toastr.error(result.message || 'فشل التحديث');
            }
        } catch (error) {
            const previousValue = select.getAttribute('data-previous-value');
            if (previousValue) select.value = previousValue;
            if (typeof toastr !== 'undefined') toastr.error('حدث خطأ أثناء الاتصال بالخادم');
        } finally {
            select.disabled = false;
            // Store current value as previous for potential revert
            select.setAttribute('data-previous-value', select.value);
        }
    }

    /**
     * Update Entity Scope (for internal-entities and authorities)
     * Handles entity_display_scope and entity_add_scope
     */
    async function updateEntityScope(select) {
        const roleId = document.querySelector('[data-role-id]')?.dataset.roleId;
        if (!roleId) return;

        const name = select.getAttribute('name');
        // Match pattern: entity_display_scope[internal_entities] or entity_add_scope[authorities]
        const matches = name.match(/entity_(display|add)_scope\[([^\]]+)\]/);
        if (!matches) {
            console.warn('Could not parse entity scope name:', name);
            return;
        }
        
        const type = matches[1]; // 'display' or 'add'
        const key = matches[2];   // 'internal_entities' or 'authorities'
        const value = select.value;

        select.disabled = true;

        try {
            const result = await apiRequest('/roles-permissions/toggle-entity-scope', {
                role_id: roleId,
                type: type,
                entity_key: key,
                value: value
            });

            if (result.success) {
                if (typeof toastr !== 'undefined') toastr.success(result.message || 'تم تحديث نطاق الجهات');
            } else {
                // Revert select to previous value
                const previousValue = select.getAttribute('data-previous-value');
                if (previousValue) select.value = previousValue;
                if (typeof toastr !== 'undefined') toastr.error(result.message || 'فشل التحديث');
            }
        } catch (error) {
            const previousValue = select.getAttribute('data-previous-value');
            if (previousValue) select.value = previousValue;
            if (typeof toastr !== 'undefined') toastr.error('حدث خطأ أثناء الاتصال بالخادم');
        } finally {
            select.disabled = false;
            select.setAttribute('data-previous-value', select.value);
        }
    }

    /**
     * Full Access Toggle
     */
    const fullAccessToggle = document.getElementById('fullAccessToggle');
    if (fullAccessToggle) {
        fullAccessToggle.addEventListener('change', async function() {
            const roleId = document.querySelector('[data-role-id]')?.dataset.roleId;
            if (!roleId) return;

            const value = this.checked ? 1 : 0;
            this.disabled = true;

            try {
                const result = await apiRequest('/roles-permissions/toggle-scope', {
                    role_id: roleId,
                    field: 'full_access',
                    value: value
                });

                if (result.success) {
                    if (typeof toastr !== 'undefined') toastr.success(result.message || 'تم تحديث الوصول الكامل');
                    updateAllPermissionsUI();
                    // Optionally reload page to reflect changes
                    // location.reload();
                } else {
                    this.checked = !this.checked;
                    if (typeof toastr !== 'undefined') toastr.error(result.message || 'فشل التحديث');
                }
            } catch (error) {
                this.checked = !this.checked;
                if (typeof toastr !== 'undefined') toastr.error('حدث خطأ أثناء الاتصال بالخادم');
            } finally {
                this.disabled = false;
            }
        });
    }

    /**
     * Event Listeners for dynamic elements
     */
    document.addEventListener('change', function(e) {
        // Handle Individual Permission Checkboxes
        if (e.target.classList.contains('perm-checkbox')) {
            togglePermission(e.target);
            syncModuleScopeVisibility(e.target.closest('.module-card'));
        }

        // Handle Module Scope Selects (administrative and geographic)
        if (e.target.classList.contains('module-scope-select') || e.target.classList.contains('geo-scope-select')) {
            updateModuleScope(e.target);
        }

        // Handle Entity Scopes (for authorities/internal-entities)
        if (e.target.classList.contains('entity-scope-select')) {
            updateEntityScope(e.target);
        }
    });

    /**
     * Store initial values for selects to enable revert on error
     */
    function storeInitialSelectValues() {
        document.querySelectorAll('select.module-scope-select, select.geo-scope-select, select.entity-scope-select').forEach(select => {
            select.setAttribute('data-previous-value', select.value);
        });
    }
    storeInitialSelectValues();

    /**
     * Bulk Update Permissions
     */
    async function bulkUpdatePermissions(checkboxes, action) {
        const roleId = document.querySelector('[data-role-id]')?.dataset.roleId;
        if (!roleId || checkboxes.length === 0) return;

        // Visual feedback
        checkboxes.forEach(cb => {
            cb.checked = (action === 'attach');
            const label = cb.closest('.modern-perm-card');
            if (label) {
                label.style.opacity = '0.5';
                if (cb.checked) label.classList.add('is-checked');
                else label.classList.remove('is-checked');
            }
        });

        try {
            // Send requests sequentially (or could use Promise.all for performance)
            for (const cb of checkboxes) {
                await apiRequest('/roles-permissions/toggle', {
                    role_id: roleId,
                    permission_id: cb.dataset.permissionId,
                    action: action
                });
            }
            if (typeof toastr !== 'undefined') toastr.success('تم تحديث الصلاحيات بنجاح');
        } catch (error) {
            if (typeof toastr !== 'undefined') toastr.error('حدث خطأ أثناء التحديث الجماعي');
            // Revert checkboxes on error? Better to reload or keep as is but show error.
        } finally {
            checkboxes.forEach(cb => {
                const label = cb.closest('.modern-perm-card');
                if (label) label.style.opacity = '1';
                syncModuleScopeVisibility(cb.closest('.module-card'));
            });
        }
    }

    // Global Select All
    const globalSelectAllBtn = document.getElementById('globalSelectAllBtn');
    if (globalSelectAllBtn) {
        globalSelectAllBtn.addEventListener('click', function() {
            const allCbs = document.querySelectorAll('.perm-checkbox:not(:checked)');
            bulkUpdatePermissions(allCbs, 'attach');
        });
    }

    // Global Deselect All
    const globalDeselectAllBtn = document.getElementById('globalDeselectAllBtn');
    if (globalDeselectAllBtn) {
        globalDeselectAllBtn.addEventListener('click', function() {
            const allCbs = document.querySelectorAll('.perm-checkbox:checked');
            bulkUpdatePermissions(allCbs, 'detach');
        });
    }

    // Module Level Select All
    document.querySelectorAll('.module-header-select-all').forEach(btn => {
        btn.addEventListener('click', function() {
            const moduleName = this.dataset.target;
            const cbs = document.querySelectorAll(`.module-perm-checkbox-${moduleName}:not(:checked)`);
            bulkUpdatePermissions(cbs, 'attach');
        });
    });

    // Module Level Deselect All
    document.querySelectorAll('.module-header-deselect-all').forEach(btn => {
        btn.addEventListener('click', function() {
            const moduleName = this.dataset.target;
            const cbs = document.querySelectorAll(`.module-perm-checkbox-${moduleName}:checked`);
            bulkUpdatePermissions(cbs, 'detach');
        });
    });

    /**
     * Sync Scope Visibility with Checked Permissions
     * Disables scope dropdowns if no permissions are checked for that module
     */
    function syncModuleScopeVisibility(moduleCard) {
        if (!moduleCard) return;
        
        const hasPermissions = moduleCard.querySelectorAll('.perm-checkbox:checked').length > 0;
        const isFullAccess = fullAccessToggle?.checked;
        const isVisible = hasPermissions || isFullAccess;

        // Standard Scope Controls (admin and geo dropdowns in side panel)
        const scopeControls = moduleCard.querySelector('.module-domain-controls');
        if (scopeControls) {
            scopeControls.style.opacity = isVisible ? '1' : '0.4';
            scopeControls.style.pointerEvents = isVisible ? 'all' : 'none';
        }

        // Dedicated Scope Cards (Projects, Planning, Correspondence, Reports, Entities)
        const dedicatedScopes = moduleCard.querySelectorAll('.projects-scope-card, .planning-scope-card, .correspondence-scope-card, .reports-scope-card, .entities-scope-card');
        dedicatedScopes.forEach(card => {
            if (isVisible) {
                card.style.opacity = '1';
                card.style.pointerEvents = 'all';
                card.style.display = '';
            } else {
                card.style.opacity = '0.4';
                card.style.pointerEvents = 'none';
                // To completely hide instead of just disabling, uncomment:
                // card.style.display = 'none';
            }
        });
    }

    /**
     * Initial Sync for all modules
     */
    function initialSync() {
        document.querySelectorAll('.module-card').forEach(card => {
            syncModuleScopeVisibility(card);
        });
    }
    initialSync();

    /**
     * Live Search Functionality
     */
    const searchInput = document.getElementById('permissionSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const modules = document.querySelectorAll('.module-card');
            
            modules.forEach(module => {
                const title = module.dataset.moduleName?.toLowerCase() || '';
                const label = module.querySelector('.module-title-text')?.textContent.toLowerCase() || '';
                const perms = module.querySelectorAll('.permission-item');
                
                let hasMatch = title.includes(query) || label.includes(query);
                
                perms.forEach(perm => {
                    const text = perm.textContent.toLowerCase();
                    if (text.includes(query)) {
                        perm.style.display = '';
                        hasMatch = true;
                    } else {
                        perm.style.display = 'none';
                    }
                });

                module.style.display = hasMatch ? '' : 'none';
            });
        });
    }

    // Optional: Handle custom geographic scope selection (if you add a modal)
    // This is a placeholder for future enhancement
    function setupCustomGeoScopeHandler() {
        document.querySelectorAll('.geo-scope-select').forEach(select => {
            select.addEventListener('change', function(e) {
                if (this.value === 'custom') {
                    // You can open a modal to select specific governorates
                    // For now, just a console log
                    console.log('Custom geographic scope selected for module:', this.dataset.module);
                    // Example: openModalForCustomGeo(this.dataset.module);
                }
            });
        });
    }
    setupCustomGeoScopeHandler();
});