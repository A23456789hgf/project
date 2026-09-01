<div class="project-table-container mt-4" id="projectEntitiesContainer">
    <div class="project-table-header">
        <i class="fas fa-list-ul"></i> جهات المشروع (تجميع تلقائي)
    </div>

    <div class="project-table-wrapper">
        <div class="project-info-text mb-3">
            <i class="fas fa-info-circle me-1"></i>
            يتم تجميع هذه القائمة تلقائياً من الجهات (الإشرافية، المنفذة، المشاركة، والمستفيدة) المضافة أعلاه.
        </div>

        <table class="project-table" id="projectEntitiesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الجهة</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($project) && $project->projectEntities->count() > 0)
                    @foreach($project->projectEntities as $index => $entity)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $entity->entity_name }}
                                <input type="hidden" name="project_entities[{{ $index }}][entity_name]" value="{{ $entity->entity_name }}">
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr class="project-empty-row">
                        <td colspan="2" class="text-center">
                            <i class="fas fa-layer-group fa-2x mb-2 text-muted"></i><br>
                            سيتم عرض الجهات المجمعة هنا تلقائياً
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const ProjectEntitiesManager = {
        init: function() {
            this.bindEvents();
            // Initial sync with a delay to ensure other Managers (Supervising, Implementing, Participating)
            // have finished their setTimeout(..., 100) initializations.
            setTimeout(() => {
                this.syncEntities();
            }, 500);
        },

        bindEvents: function() {
            // Listen for changes in the select elements across all source tables
            $(document).on('change select2:select select2:unselect select2:clear', '.authority-select, .entity-type-select, .authority-type-select', () => {
                setTimeout(() => ProjectEntitiesManager.syncEntities(), 100);
            });


            // Generic fallback for any row removal or addition (MutationObserver)
            const observer = new MutationObserver((mutations) => {
                let shouldSync = false;
                mutations.forEach(mutation => {
                    if (mutation.type === 'childList') {
                        // Safely check if the target is an Element node (nodeType 1)
                        if (mutation.target && mutation.target.nodeType === 1) {
                            const table = mutation.target.closest('table');
                            if (table && ['supervisingAuthoritiesTable', 'implementingEntitiesTable', 'participatingEntitiesTable', 'beneficiaryEntitiesTable'].includes(table.id)) {
                                shouldSync = true;
                            }
                        }
                    }
                });
                if (shouldSync) {
                    setTimeout(() => ProjectEntitiesManager.syncEntities(), 300);
                }
            });

            const config = { childList: true, subtree: true };
            const tables = ['supervisingAuthoritiesTable', 'implementingEntitiesTable', 'participatingEntitiesTable', 'beneficiaryEntitiesTable'];
            tables.forEach(id => {
                const el = document.getElementById(id);
                if (el) observer.observe(el, config);
            });
        },

        syncEntities: function() {
            if (this._syncTimeout) clearTimeout(this._syncTimeout);
            this._syncTimeout = setTimeout(() => {
                console.log('🔄 ProjectEntitiesManager: Starting syncEntities()...');
                const entities = new Set();
                
                // Collect from all source tables (supervising, implementing, participating, beneficiary)
                const tableIds = ['supervisingAuthoritiesTable', 'implementingEntitiesTable', 'participatingEntitiesTable', 'beneficiaryEntitiesTable'];
                
                tableIds.forEach(id => {
                    const table = document.getElementById(id);
                    if (!table) return;

                    // Skip if the table's parent section is hidden (e.g. beneficiary section hidden for "new" projects)
                    const parentSection = table.closest('[id$="-section"]');
                    if (parentSection && parentSection.style.display === 'none') return;
                    
                    let selectCount = 0;
                    // Get all select elements with class authority-select (avoid Select2 span containers)
                    $(table).find('select.authority-select').each(function() {
                        selectCount++;
                        const $select = $(this);
                        
                        // 1. Try Select2 data first
                        if ($select.hasClass('select2-hidden-accessible')) {
                            const data = $select.select2('data');
                            if (data && data.length > 0 && data[0].text && data[0].id) {
                                entities.add(data[0].text.trim());
                            }
                            return; // If it is a Select2, don't fall back to standard option reading
                        }
                        
                        // 2. Fallback to existing selected option text (useful for resume/initial load or non-select2)
                        const selectedOption = this.options[this.selectedIndex];
                        if (selectedOption && selectedOption.value && selectedOption.text && !selectedOption.text.includes('اختر')) {
                            entities.add(selectedOption.text.trim());
                        }
                    });
                });

                this.updateTable(Array.from(entities));
            }, 300); // 300ms debounce
        },

        updateTable: function(entityNames) {

            const tbody = document.querySelector('#projectEntitiesTable tbody');
            if (!tbody) return;
            
            if (entityNames.length === 0) {
                tbody.innerHTML = `
                    <tr class="project-empty-row">
                        <td colspan="2" class="text-center">
                            <i class="fas fa-layer-group fa-2x mb-2 text-muted"></i><br>
                            سيتم عرض الجهات المجمعة هنا تلقائياً
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            entityNames.forEach((name, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            ${name}
                            <input type="hidden" name="project_entities[${index}][entity_name]" value="${name}">
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }
    };

    ProjectEntitiesManager.init();
    window.ProjectEntitiesManager = ProjectEntitiesManager;
});
</script>
