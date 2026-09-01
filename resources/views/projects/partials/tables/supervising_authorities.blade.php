<div class="mb-4">
    <div class="project-table-header mb-3">
        <i class="fas fa-users-cog"></i> الجهات الإشرافية
    </div>

    <div class="table-responsive">
        <table class="project-table w-100" id="supervisingAuthoritiesTable">
            <thead>
                <tr>
                    <th>نوع الجهة</th>
                    <th>اسم الجهة</th>
                    <th>الجهة الأم</th>
                    <th width="100">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($project) && $project->supervisingAuthorities->count() > 0)
                    @foreach($project->supervisingAuthorities as $index => $authority)
                        <tr data-authority-index="{{ $index }}">
                            <td>
                                <select name="supervising_authorities[{{ $index }}][authority_type]"
                                        class="form-select authority-type-select">
                                    <option value="internal" {{ $authority->authority_type == 'internal' ? 'selected' : '' }}>داخلية</option>
                                    <option value="external" {{ $authority->authority_type == 'external' ? 'selected' : '' }}>خارجية</option>
                                </select>
                            </td>
                            <td>
                                @php
                                    $entityId = null;
                                    $entityName = null;
                                    $fieldName = 'internal_entity_id';

                                    if ($authority->authority_type == 'internal') {
                                        $entityId = $authority->internal_entity_id;
                                        $entityName = $authority->internalEntity ? $authority->internalEntity->name : null;
                                        $fieldName = 'internal_entity_id';
                                    } else {
                                        $entityId = $authority->authority_id;
                                        $entityName = $authority->authority ? $authority->authority->agency_name : null;
                                        $fieldName = 'authority_id';
                                    }
                                @endphp
                                <select name="supervising_authorities[{{ $index }}][{{ $fieldName }}]"
                                        class="form-select authority-select"
                                        data-type="{{ $authority->authority_type }}"
                                        data-authority-id="{{ $entityId }}">
                                    <option value="">اختر الجهة</option>
                                    @if($authority->authority_type == 'internal')
                                        @foreach($internalEntities ?? [] as $internal)
                                            <option value="{{ $internal['id'] }}"
                                                {{ $entityId == $internal['id'] ? 'selected' : '' }}>
                                                {{ $internal['entity_name'] ?? $internal['name'] ?? '' }}
                                            </option>
                                        @endforeach
                                    @else
                                        @foreach($authorities ?? [] as $authItem)
                                            <option value="{{ $authItem->id }}"
                                                {{ $entityId == $authItem->id ? 'selected' : '' }}>
                                                {{ $authItem->agency_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </td>
                            <td>
                                @php
                                    $parentName = 'لا توجد جهة أب';
                                    $parentId = null;

                                    if ($authority->authority_type == 'internal') {
                                        $internalEntity = $authority->internalEntity ?? \App\Models\InternalEntity::withoutGlobalScopes()->find($entityId);
                                        if ($internalEntity) {
                                            $parent = $internalEntity->parent()->first();
                                            if ($parent) {
                                                $parentName = $parent->name;
                                                $parentId = $parent->id;
                                            }
                                        }
                                    } else {
                                        $authorityModel = $authority->authority ?? \App\Models\Authority::withoutGlobalScopes()->find($entityId);
                                        if ($authorityModel) {
                                            $parent = $authorityModel->parent()->first();
                                            if ($parent) {
                                                $parentName = $parent->agency_name ?? $parent->name;
                                                $parentId = $parent->id;
                                            }
                                        }
                                    }
                                @endphp
                                <input type="text"
                                       name="supervising_authorities[{{ $index }}][parent_name]"
                                       class="form-control parent-name-input"
                                       readonly
                                       value="{{ $parentName }}"
                                       placeholder="سيتم تعبئته تلقائياً">
                                <input type="hidden"
                                       name="supervising_authorities[{{ $index }}][parent_id]"
                                       class="parent-id-input"
                                       value="{{ $parentId }}">
                            </td>
                            <td>
                                <div class="project-action-buttons">
                                    <button type="button" class="project-btn project-btn-danger remove-authority" title="حذف الجهة">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr class="project-empty-row">
                        <td colspan="4" class="text-center">
                            <i class="fas fa-users-cog fa-2x mb-2 text-muted"></i><br>
                            لا توجد جهات إشرافية مضافة بعد
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="p-3">
            <button type="button" class="project-btn project-btn-primary" id="addSupervisingAuthorityBtn">
                <i class="fas fa-plus"></i>إضافة جهة إشرافية جديدة
            </button>
        </div>
    </div>
</div>

<script>
// Supervising Authorities Management Module
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Get data from PHP
        var authorities = @json($authorities ?? []);
        var internalEntities = @json($internalEntities ?? []);

        console.log('Supervising Authorities - Authorities count:', authorities.length);
        console.log('Supervising Authorities - Internal Entities count:', internalEntities.length);

        var SupervisingAuthorityManager = {
            counter: 0,
            authorities: authorities,
            internalEntities: internalEntities,

            init: function() {
                console.log('Initializing SupervisingAuthorityManager');

                var rows = document.querySelectorAll('#supervisingAuthoritiesTable tbody tr[data-authority-index]');
                rows.forEach(function(row) {
                    var idx = parseInt(row.dataset.authorityIndex) || 0;
                    if (idx >= this.counter) this.counter = idx + 1;
                }.bind(this));

                this.bindEvents();
                this.initializeExistingEntities();
            },

            bindEvents: function() {
                var addBtn = document.getElementById('addSupervisingAuthorityBtn');
                if (addBtn) {
                    addBtn.addEventListener('click', function() {
                        this.addRow();
                    }.bind(this));
                }

                document.addEventListener('click', function(e) {
                    var btn = e.target.closest('.remove-authority');
                    if (!btn || !btn.closest('#supervisingAuthoritiesTable')) return;
                    this.removeRow(btn.closest('tr'));
                }.bind(this));

                document.addEventListener('change', function(e) {
                    if (!e.target.closest('#supervisingAuthoritiesTable')) return;

                    if (e.target.classList.contains('authority-type-select')) {
                        this.handleEntityTypeChange(e.target);
                    } else if (e.target.classList.contains('authority-select')) {
                        this.handleAuthorityChange(e.target);
                    }
                }.bind(this));
            },

            initializeExistingEntities: function() {
                var rows = document.querySelectorAll('#supervisingAuthoritiesTable tbody tr:not(.project-empty-row)');
                rows.forEach(function(row) {
                    var typeSelect = row.querySelector('.authority-type-select');
                    var authoritySelect = row.querySelector('.authority-select');

                    if (typeSelect && authoritySelect) {
                        var entityType = typeSelect.value;
                        var savedValue = authoritySelect.getAttribute('data-authority-id') || authoritySelect.value;

                        this.populateAuthorityOptions(authoritySelect, entityType);

                        if (savedValue) {
                            authoritySelect.value = savedValue;
                            this.fillParentAuthority(row, savedValue, entityType);
                        }
                    }
                }.bind(this));
            },

            addRow: function() {
                var emptyRow = document.querySelector('#supervisingAuthoritiesTable .project-empty-row');
                if (emptyRow) emptyRow.remove();

                var tbody = document.querySelector('#supervisingAuthoritiesTable tbody');
                var row = this.createRow();
                tbody.appendChild(row);

                if (typeof AppUtils !== 'undefined' && AppUtils.Utils) {
                    AppUtils.Utils.animate(row, 'fadeIn');
                }
                this.counter++;
            },

            createRow: function() {
                var row = document.createElement('tr');
                var index = this.counter;
                row.dataset.authorityIndex = index;

                // Build internal entity options
                var internalOptions = '<option value="">اختر الجهة</option>';
                this.internalEntities.forEach(function(entity) {
                    internalOptions += '<option value="' + entity.id + '">' + (entity.entity_name || entity.name || '') + '</option>';
                });

                row.innerHTML = `
                    <td>
                        <select name="supervising_authorities[${index}][authority_type]"
                                class="form-select authority-type-select">
                            <option value="internal" selected>داخلية</option>
                            <option value="external">خارجية</option>
                        </select>
                    </td>
                    <td>
                        <select name="supervising_authorities[${index}][internal_entity_id]"
                                class="form-select authority-select"
                                data-type="internal">
                            ${internalOptions}
                        </select>
                    </td>
                    <td>
                        <input type="text"
                               name="supervising_authorities[${index}][parent_name]"
                               class="form-control parent-name-input"
                               readonly
                               placeholder="سيتم تعبئته تلقائياً">
                        <input type="hidden"
                               name="supervising_authorities[${index}][parent_id]"
                               class="parent-id-input">
                    </td>
                    <td>
                        <div class="project-action-buttons">
                            <button type="button" class="project-btn project-btn-danger remove-authority" title="حذف الجهة">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;

                return row;
            },

            removeRow: function(row) {
                var self = this;
                if (typeof AppUtils !== 'undefined' && AppUtils.Table) {
                    AppUtils.Table.removeRow(row, false).then(function() {
                        self.checkEmpty();
                        self.renumber();
                    });
                } else {
                    row.remove();
                    this.checkEmpty();
                    this.renumber();
                }
            },

            handleEntityTypeChange: function(select) {
                var row = select.closest('tr');
                var authoritySelect = row.querySelector('.authority-select');
                var type = select.value;
                var index = row.dataset.authorityIndex;
                var fieldName = type === 'internal' ? 'internal_entity_id' : 'authority_id';

                authoritySelect.setAttribute('name', 'supervising_authorities[' + index + '][' + fieldName + ']');
                authoritySelect.setAttribute('data-type', type);

                this.populateAuthorityOptions(authoritySelect, type);
                authoritySelect.value = '';
                this.clearParentFields(row);
            },

            handleAuthorityChange: function(select) {
                var row = select.closest('tr');
                var typeSelect = row.querySelector('.authority-type-select');
                var type = typeSelect ? typeSelect.value : 'internal';

                console.log('Supervising Authority changed:', select.value, 'Type:', type);
                this.fillParentAuthority(row, select.value, type);
            },

            populateAuthorityOptions: function(authoritySelect, type) {
                var options = '<option value="">اختر الجهة</option>';

                if (type === 'internal') {
                    console.log('Populating internal entities for supervising authorities:', this.internalEntities.length);
                    this.internalEntities.forEach(function(entity) {
                        options += '<option value="' + entity.id + '">' + (entity.entity_name || entity.name || '') + '</option>';
                    });
                } else {
                    console.log('Populating external authorities for supervising authorities:', this.authorities.length);
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

            checkEmpty: function() {
                var tbody = document.querySelector('#supervisingAuthoritiesTable tbody');
                if (tbody.children.length === 0 || (tbody.children.length === 1 && tbody.children[0].classList.contains('project-empty-row'))) {
                    return;
                }

                if (tbody.querySelectorAll('tr:not(.project-empty-row)').length === 0) {
                    var emptyRow = document.createElement('tr');
                    emptyRow.className = 'project-empty-row';
                    emptyRow.innerHTML = `
                        <td colspan="4" class="text-center">
                            <i class="fas fa-users-cog fa-2x mb-2 text-muted"></i><br>
                            لا توجد جهات إشرافية مضافة بعد
                        </td>
                    `;
                    tbody.appendChild(emptyRow);
                }
            },

            renumber: function() {
                var rows = document.querySelectorAll('#supervisingAuthoritiesTable tbody tr:not(.project-empty-row)');
                var newCounter = 0;

                rows.forEach(function(row, index) {
                    row.dataset.authorityIndex = index;

                    var typeSelect = row.querySelector('.authority-type-select');
                    var authoritySelect = row.querySelector('.authority-select');
                    var parentNameInput = row.querySelector('.parent-name-input');
                    var parentIdInput = row.querySelector('.parent-id-input');

                    var currentType = typeSelect ? typeSelect.value : 'internal';
                    var fieldName = currentType === 'internal' ? 'internal_entity_id' : 'authority_id';

                    if (typeSelect) typeSelect.name = 'supervising_authorities[' + index + '][authority_type]';
                    if (authoritySelect) authoritySelect.name = 'supervising_authorities[' + index + '][' + fieldName + ']';
                    if (parentNameInput) parentNameInput.name = 'supervising_authorities[' + index + '][parent_name]';
                    if (parentIdInput) parentIdInput.name = 'supervising_authorities[' + index + '][parent_id]';

                    newCounter++;
                });

                this.counter = newCounter;
            }
        };

        SupervisingAuthorityManager.init();
        window.SupervisingAuthorityManager = SupervisingAuthorityManager;
    });
})();
</script>

<style>
/* Additional styles for supervising authorities table */
#supervisingAuthoritiesTable .parent-name-input {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    cursor: not-allowed;
}

#supervisingAuthoritiesTable .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#supervisingAuthoritiesTable .project-action-buttons {
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