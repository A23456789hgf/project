<div class="mb-4">
    <div class="project-table-header mb-3">
        <i class="fas fa-cogs"></i> الجهات المنفذة
    </div>

    <div class="table-responsive">
        <table class="project-table w-100" id="implementingEntitiesTable">
            <thead>
                <tr>
                    <th>نوع الجهة</th>
                    <th>اسم الجهة</th>
                    <th>الجهة الأم</th>
                    <th width="100">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($project) && $project->implementingEntities->count() > 0)
                    @foreach($project->implementingEntities as $index => $entity)
                        <tr data-entity-index="{{ $index }}">
                            <td>
                                <select name="implementing_entities[{{ $index }}][authority_type]"
                                        class="form-select entity-type-select">
                                    <option value="internal" {{ $entity->authority_type == 'internal' ? 'selected' : '' }}>داخلية</option>
                                    <option value="external" {{ $entity->authority_type == 'external' ? 'selected' : '' }}>خارجية</option>
                                </select>
                            </td>
                            <td>
                                @php
                                    $entityId = null;
                                    $entityName = null;
                                    $fieldName = 'internal_entity_id';

                                    if ($entity->authority_type == 'internal') {
                                        $entityId = $entity->internal_entity_id;
                                        $entityName = $entity->internalEntity ? $entity->internalEntity->name : null;
                                        $fieldName = 'internal_entity_id';
                                    } else {
                                        $entityId = $entity->authority_id;
                                        $entityName = $entity->authority ? $entity->authority->agency_name : null;
                                        $fieldName = 'authority_id';
                                    }
                                @endphp
                                <select name="implementing_entities[{{ $index }}][{{ $fieldName }}]"
                                        class="form-select authority-select"
                                        data-type="{{ $entity->authority_type }}"
                                        data-entity-id="{{ $entityId }}">
                                    <option value="">اختر الجهة</option>
                                    @if($entity->authority_type == 'internal')
                                        @foreach($internalEntities ?? [] as $internal)
                                            <option value="{{ $internal['id'] }}"
                                                {{ $entityId == $internal['id'] ? 'selected' : '' }}>
                                                {{ $internal['entity_name'] ?? $internal['name'] ?? '' }}
                                            </option>
                                        @endforeach
                                    @else
                                        @foreach($authorities ?? [] as $authority)
                                            <option value="{{ $authority->id }}"
                                                {{ $entityId == $authority->id ? 'selected' : '' }}>
                                                {{ $authority->agency_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </td>
                            <td>
                                @php
                                    $parentName = 'لا توجد جهة أب';
                                    $parentId = null;

                                    if ($entity->authority_type == 'internal') {
                                        // محاولة الحصول على الجهة الداخلية من العلاقة، وإلا استعلام مباشر
                                        $internalEntity = $entity->internalEntity ?? \App\Models\InternalEntity::withoutGlobalScopes()->find($entityId);
                                        if ($internalEntity) {
                                            // استعلام صريح للحصول على الأب بدلاً من استخدام الخاصية (لتجنب lazy loading)
                                            $parent = $internalEntity->parent()->first();
                                            if ($parent) {
                                                $parentName = $parent->name;
                                                $parentId = $parent->id;
                                            }
                                        }
                                    } else {
                                        $authority = $entity->authority ?? \App\Models\Authority::withoutGlobalScopes()->find($entityId);
                                        if ($authority) {
                                            $parent = $authority->parent()->first();
                                            if ($parent) {
                                                $parentName = $parent->agency_name ?? $parent->name;
                                                $parentId = $parent->id;
                                            }
                                        }
                                    }
                                @endphp
                                <input type="text"
                                       name="implementing_entities[{{ $index }}][parent_name]"
                                       class="form-control parent-name-input"
                                       readonly
                                       value="{{ $parentName }}"
                                       placeholder="سيتم تعبئته تلقائياً">
                                <input type="hidden"
                                       name="implementing_entities[{{ $index }}][parent_id]"
                                       class="parent-id-input"
                                       value="{{ $parentId }}">
                            </td>
                            <td>
                                <div class="project-action-buttons">
                                    <button type="button" class="project-btn project-btn-danger remove-entity" title="حذف الجهة">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr class="project-empty-row">
                        <td colspan="4" class="text-center">
                            <i class="fas fa-cogs fa-2x mb-2 text-muted"></i><br>
                            لا توجد جهات منفذة مضافة بعد
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="p-3">
            <button type="button" class="project-btn project-btn-primary" id="addImplementingEntityBtn">
                <i class="fas fa-plus"></i>إضافة جهة منفذة جديدة
            </button>
        </div>
    </div>
</div>

<script>
// Implementing Entities Management Module
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Get data from PHP
        var authorities = @json($authorities ?? []);
        var internalEntities = @json($internalEntities ?? []);

        console.log('Authorities count:', authorities.length);
        console.log('Internal Entities count:', internalEntities.length);

        var ImplementingEntityManager = {
            counter: 0,
            authorities: authorities,
            internalEntities: internalEntities,

            init: function() {
                console.log('Initializing ImplementingEntityManager');

                var rows = document.querySelectorAll('#implementingEntitiesTable tbody tr[data-entity-index]');
                rows.forEach(function(row) {
                    var idx = parseInt(row.dataset.entityIndex) || 0;
                    if (idx >= this.counter) this.counter = idx + 1;
                }.bind(this));

                this.bindEvents();
                this.initializeExistingEntities();
            },

            bindEvents: function() {
                var addBtn = document.getElementById('addImplementingEntityBtn');
                if (addBtn) {
                    addBtn.addEventListener('click', function() {
                        this.addEntity();
                    }.bind(this));
                }

                document.addEventListener('click', function(e) {
                    var btn = e.target.closest('.remove-entity');
                    if (!btn || !btn.closest('#implementingEntitiesTable')) return;
                    this.removeEntity(btn.closest('tr'));
                }.bind(this));

                document.addEventListener('change', function(e) {
                    if (!e.target.closest('#implementingEntitiesTable')) return;

                    if (e.target.classList.contains('entity-type-select')) {
                        this.handleEntityTypeChange(e.target);
                    } else if (e.target.classList.contains('authority-select')) {
                        this.handleAuthorityChange(e.target);
                    }
                }.bind(this));
            },

            initializeExistingEntities: function() {
                var rows = document.querySelectorAll('#implementingEntitiesTable tbody tr:not(.project-empty-row)');
                rows.forEach(function(row) {
                    var typeSelect = row.querySelector('.entity-type-select');
                    var authoritySelect = row.querySelector('.authority-select');

                    if (typeSelect && authoritySelect) {
                        var entityType = typeSelect.value;
                        var savedValue = authoritySelect.getAttribute('data-entity-id') || authoritySelect.value;

                        this.populateAuthorityOptions(authoritySelect, entityType);

                        if (savedValue) {
                            authoritySelect.value = savedValue;
                            this.fillParentAuthority(row, savedValue, entityType);
                        }
                    }
                }.bind(this));
            },

            addEntity: function() {
                var emptyRow = document.querySelector('.project-empty-row');
                if (emptyRow) emptyRow.remove();

                var tbody = document.querySelector('#implementingEntitiesTable tbody');
                var row = this.createEntityRow();
                tbody.appendChild(row);

                if (typeof AppUtils !== 'undefined' && AppUtils.Utils) {
                    AppUtils.Utils.animate(row, 'fadeIn');
                }
                this.counter++;
            },

            createEntityRow: function() {
                var row = document.createElement('tr');
                var index = this.counter;
                row.dataset.entityIndex = index;

                // Build internal entity options
                var internalOptions = '<option value="">اختر الجهة</option>';
                this.internalEntities.forEach(function(entity) {
                    internalOptions += '<option value="' + entity.id + '">' + (entity.entity_name || entity.name || '') + '</option>';
                });

                // Build external authority options
                var externalOptions = '<option value="">اختر الجهة</option>';
                this.authorities.forEach(function(auth) {
                    externalOptions += '<option value="' + auth.id + '">' + (auth.agency_name || '') + '</option>';
                });

                row.innerHTML = `
                    <td>
                        <select name="implementing_entities[${index}][authority_type]"
                                class="form-select entity-type-select">
                            <option value="internal" selected>داخلية</option>
                            <option value="external">خارجية</option>
                        </select>
                    </td>
                    <td>
                        <select name="implementing_entities[${index}][internal_entity_id]"
                                class="form-select authority-select"
                                data-type="internal">
                            ${internalOptions}
                        </select>
                    </td>
                    <td>
                        <input type="text"
                               name="implementing_entities[${index}][parent_name]"
                               class="form-control parent-name-input"
                               readonly
                               placeholder="سيتم تعبئته تلقائياً">
                        <input type="hidden"
                               name="implementing_entities[${index}][parent_id]"
                               class="parent-id-input">
                    </td>
                    <td>
                        <div class="project-action-buttons">
                            <button type="button" class="project-btn project-btn-danger remove-entity" title="حذف الجهة">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;

                return row;
            },

            removeEntity: function(row) {
                var self = this;
                if (typeof AppUtils !== 'undefined' && AppUtils.Table) {
                    AppUtils.Table.removeRow(row, false).then(function() {
                        self.checkEmptyTable();
                        self.renumberRows();
                    });
                } else {
                    row.remove();
                    this.checkEmptyTable();
                    this.renumberRows();
                }
            },

            handleEntityTypeChange: function(select) {
                var row = select.closest('tr');
                var authoritySelect = row.querySelector('.authority-select');
                var type = select.value;
                var index = row.dataset.entityIndex;
                var fieldName = type === 'internal' ? 'internal_entity_id' : 'authority_id';

                authoritySelect.setAttribute('name', 'implementing_entities[' + index + '][' + fieldName + ']');
                authoritySelect.setAttribute('data-type', type);

                this.populateAuthorityOptions(authoritySelect, type);
                authoritySelect.value = '';
                this.clearParentFields(row);
            },

            handleAuthorityChange: function(select) {
                var row = select.closest('tr');
                var typeSelect = row.querySelector('.entity-type-select');
                var type = typeSelect ? typeSelect.value : 'internal';

                console.log('Authority changed:', select.value, 'Type:', type);
                this.fillParentAuthority(row, select.value, type);
            },

            populateAuthorityOptions: function(authoritySelect, type) {
                var options = '<option value="">اختر الجهة</option>';

                if (type === 'internal') {
                    console.log('Populating internal entities:', this.internalEntities.length);
                    this.internalEntities.forEach(function(entity) {
                        options += '<option value="' + entity.id + '">' + (entity.entity_name || entity.name || '') + '</option>';
                    });
                } else {
                    console.log('Populating external authorities:', this.authorities.length);
                    this.authorities.forEach(function(auth) {
                        options += '<option value="' + auth.id + '">' + (auth.agency_name || '') + '</option>';
                    });
                }

                authoritySelect.innerHTML = options;
            },

            fillParentAuthority: function(row, authorityValue, type) {
                var parentNameInput = row.querySelector('.parent-name-input');
                var parentIdInput = row.querySelector('.parent-id-input');

                this.clearParentFields(row);

                if (!authorityValue) {
                    parentNameInput.value = 'لا توجد جهة أب';
                    return;
                }

                if (type === 'internal') {
                    var selectedEntity = null;
                    for (var i = 0; i < this.internalEntities.length; i++) {
                        if (this.internalEntities[i].id == authorityValue) {
                            selectedEntity = this.internalEntities[i];
                            break;
                        }
                    }
                    console.log('Selected internal entity:', selectedEntity);

                    if (selectedEntity) {
                        // Support both 'parent_name' (DB query) and 'father_name' (JSON file) field names
                        var pName = selectedEntity.parent_name || selectedEntity.father_name || null;
                        if (selectedEntity.parent_id && pName) {
                            parentNameInput.value = pName;
                            parentIdInput.value = selectedEntity.parent_id;
                        } else {
                            parentNameInput.value = 'لا توجد جهة أب';
                            parentIdInput.value = '';
                        }
                    }
                } else {
                    var selectedAuthority = null;
                    for (var j = 0; j < this.authorities.length; j++) {
                        if (this.authorities[j].id == authorityValue) {
                            selectedAuthority = this.authorities[j];
                            break;
                        }
                    }
                    console.log('Selected external authority:', selectedAuthority);

                    if (selectedAuthority) {
                        if (selectedAuthority.parent_id) {
                            var parentAuthority = null;
                            for (var k = 0; k < this.authorities.length; k++) {
                                if (this.authorities[k].id == selectedAuthority.parent_id) {
                                    parentAuthority = this.authorities[k];
                                    break;
                                }
                            }
                            if (parentAuthority) {
                                parentNameInput.value = parentAuthority.agency_name || '';
                                parentIdInput.value = parentAuthority.id || '';
                            } else {
                                parentNameInput.value = 'لا توجد جهة أب';
                                parentIdInput.value = '';
                            }
                        } else {
                            parentNameInput.value = 'لا توجد جهة أب';
                            parentIdInput.value = '';
                        }
                    }
                }
            },

            clearParentFields: function(row) {
                var parentNameInput = row.querySelector('.parent-name-input');
                var parentIdInput = row.querySelector('.parent-id-input');
                if (parentNameInput) parentNameInput.value = '';
                if (parentIdInput) parentIdInput.value = '';
            },

            checkEmptyTable: function() {
                var tbody = document.querySelector('#implementingEntitiesTable tbody');
                if (tbody.children.length === 0) {
                    tbody.innerHTML = `
                        <tr class="project-empty-row">
                            <td colspan="4" class="text-center">
                                <i class="fas fa-cogs fa-2x mb-2 text-muted"></i><br>
                                لا توجد جهات منفذة مضافة بعد
                            </td>
                        </tr>
                    `;
                }
            },

            renumberRows: function() {
                var rows = document.querySelectorAll('#implementingEntitiesTable tbody tr:not(.project-empty-row)');
                var newCounter = 0;

                rows.forEach(function(row, index) {
                    row.dataset.entityIndex = index;

                    var entityTypeSelect = row.querySelector('.entity-type-select');
                    var authoritySelect = row.querySelector('.authority-select');
                    var parentNameInput = row.querySelector('.parent-name-input');
                    var parentIdInput = row.querySelector('.parent-id-input');

                    var currentType = entityTypeSelect ? entityTypeSelect.value : 'internal';
                    var fieldName = currentType === 'internal' ? 'internal_entity_id' : 'authority_id';

                    if (entityTypeSelect) entityTypeSelect.name = 'implementing_entities[' + index + '][authority_type]';
                    if (authoritySelect) authoritySelect.name = 'implementing_entities[' + index + '][' + fieldName + ']';
                    if (parentNameInput) parentNameInput.name = 'implementing_entities[' + index + '][parent_name]';
                    if (parentIdInput) parentIdInput.name = 'implementing_entities[' + index + '][parent_id]';

                    newCounter++;
                });

                this.counter = newCounter;
            },

            getEntitiesData: function() {
                var entities = [];
                var rows = document.querySelectorAll('#implementingEntitiesTable tbody tr:not(.project-empty-row)');

                rows.forEach(function(row) {
                    var entityType = row.querySelector('.entity-type-select')?.value;
                    var authoritySelect = row.querySelector('.authority-select');
                    var authorityId = authoritySelect?.value;
                    var parentId = row.querySelector('.parent-id-input')?.value;

                    if (entityType && authorityId) {
                        var item = {
                            authority_type: entityType,
                            parent_id: parentId || null
                        };

                        if (entityType === 'internal') {
                            item.internal_entity_id = authorityId;
                        } else {
                            item.authority_id = authorityId;
                        }
                        entities.push(item);
                    }
                });

                return entities;
            },

            validateEntities: function() {
                var entities = this.getEntitiesData();

                if (entities.length === 0) {
                    return { valid: false, message: 'يجب إضافة جهة منفذة واحدة على الأقل' };
                }

                var ids = [];
                for (var i = 0; i < entities.length; i++) {
                    var id = entities[i].authority_type === 'internal' ? entities[i].internal_entity_id : entities[i].authority_id;
                    ids.push(id);
                }
                var uniqueIds = [...new Set(ids)];

                if (ids.length !== uniqueIds.length) {
                    return { valid: false, message: 'لا يمكن اختيار نفس الجهة أكثر من مرة' };
                }

                return { valid: true, entities: entities };
            }
        };

        // Initialize the manager
        ImplementingEntityManager.init();
        window.ImplementingEntityManager = ImplementingEntityManager;
    });
})();
</script>

<style>
/* Additional styles for implementing entities table */
#implementingEntitiesTable .parent-name-input {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    cursor: not-allowed;
}

#implementingEntitiesTable .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#implementingEntitiesTable .project-action-buttons {
    display: flex;
    gap: 5px;
    justify-content: center;
}

/* Responsive design */
@media (max-width: 768px) {
    .table-responsive {
        margin: 10px 0;
        overflow-x: auto;
    }

    .project-action-buttons {
        flex-direction: column;
    }
}
</style>
