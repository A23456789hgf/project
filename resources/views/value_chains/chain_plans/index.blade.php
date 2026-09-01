@extends('layouts.app')

@section('content')
    {{-- Custom styles for internal elements (Table, Buttons, Filters) --}}
        <style>
            /* Modern Filter Inputs */
            .modern-filter-input {
                border-radius: 8px;
                border: 1px solid #e0e4e8;
                padding: 0.5rem 0.75rem;
                transition: all 0.2s ease;
            }
            .modern-filter-input:focus {
                border-color: #86b7fe;
                box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            }

            /* Input Group Search */
            .search-group .input-group-text {
                background-color: transparent;
                border-color: #e0e4e8;
                border-radius: 0 8px 8px 0;
            }
            .search-group .form-control {
                border-color: #e0e4e8;
                border-radius: 8px 0 0 8px;
            }
            .search-group .form-control:focus + .input-group-text,
            .search-group .form-control:focus {
                border-color: #86b7fe;
            }

            /* Sortable Table Headers */
            .sortable-header {
                color: #495057;
                font-weight: 600;
                transition: color 0.2s;
            }
            .sortable-header:hover {
                color: #0d6efd;
            }
            .sortable-header .icon {
                opacity: 0.5;
                transition: opacity 0.2s;
            }
            .sortable-header:hover .icon {
                opacity: 1;
            }

            /* Soft Action Buttons */
            .btn-soft {
                width: 32px;
                height: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                border: none;
                background-color: transparent;
                transition: all 0.2s ease;
            }
            .btn-soft-primary { color: #0d6efd; }
            .btn-soft-primary:hover { background-color: rgba(13, 110, 253, 0.1); }

            .btn-soft-success { color: #198754; }
            .btn-soft-success:hover { background-color: rgba(25, 135, 84, 0.1); }

            .btn-soft-danger { color: #dc3545; }
            .btn-soft-danger:hover { background-color: rgba(220, 53, 69, 0.1); }

            .btn-soft-secondary { color: #6c757d; }
            .btn-soft-secondary:hover { background-color: rgba(108, 117, 125, 0.1); }
        </style>

        <x-index-page title="خطط السلاسل" icon="layer-group">

            {{-- ========================================== --}}
            {{-- Header Actions Slot --}}
            {{-- ========================================== --}}
            <x-slot name="headerActions">
                @can('chain_plans.create')
                    <a href="{{ route('chain_plans.create') }}"
                        class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2 transition-all">
                        <x-icon name="plus" size="16" /> إضافة خطة جديدة
                    </a>
                @endcan

                @if(auth()->user()->can('chain_plans.export') || auth()->user()->can('chain_plans.import'))
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-dark px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2"
                        type="button" data-bs-toggle="dropdown">
                        <x-icon name="database" size="16" /> البيانات
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px;">
                        @can('chain_plans.export')
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" href="{{ route('chain_plans.export') }}">
                                <x-icon name="download" size="16" class="text-success" /> تصدير Excel
                            </a>
                        </li>
                        @endcan
                        @if(auth()->user()->can('chain_plans.export') && auth()->user()->can('chain_plans.import'))
                        <li><hr class="dropdown-divider opacity-50"></li>
                        @endif
                        @can('chain_plans.import')
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" href="{{ route('chain_plans.import') }}">
                                <x-icon name="upload" size="16" class="text-info" /> استيراد ملف
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
                @endif

                @can('chain_plans.batch-print')
                <a href="{{ route('chain_plans.batch_print') }}"
                    class="btn btn-light border px-3 py-2 rounded-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2 text-secondary">
                    <x-icon name="print" size="16" /> طباعة شاملة
                </a>
                @endcan
            </x-slot>

            {{-- ========================================== --}}
            {{-- Filters Slot --}}
            {{-- ========================================== --}}
            <x-slot name="filters">
                <form method="GET" action="{{ route('chain_plans.index') }}" class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label text-secondary small fw-bold mb-1">السلسلة</label>
                        <select name="value_chain_id" class="form-select modern-filter-input">
                            <option value="">كل السلاسل</option>
                            @foreach($valueChains as $vc)
                                <option value="{{ $vc->id }}" {{ request('value_chain_id') == $vc->id ? 'selected' : '' }}>
                                    {{ $vc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary small fw-bold mb-1">المحافظة</label>
                        <select name="governorate_id" id="filter-governorate" class="form-select modern-filter-input">
                            <option value="">كل المحافظات</option>
                            @foreach($governorates as $gov)
                                <option value="{{ $gov->id }}" {{ request('governorate_id') == $gov->id ? 'selected' : '' }}>
                                    {{ $gov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary small fw-bold mb-1">المديرية</label>
                        <select name="directorate_id" id="filter-directorate" class="form-select modern-filter-input">
                            <option value="">كل المديريات</option>
                            @if(isset($directorates) && $directorates->count())
                                @foreach($directorates as $dir)
                                    <option value="{{ $dir->id }}" {{ request('directorate_id') == $dir->id ? 'selected' : '' }}>
                                        {{ $dir->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary small fw-bold mb-1">بحث</label>
                        <div class="input-group search-group">
                            <span class="input-group-text">
                                <x-icon name="search" size="16" class="text-muted" />
                            </span>
                            <input type="text" name="search" class="form-control"
                                placeholder="بحث بالمؤشر، المحافظة..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-dark px-4 fw-bold rounded-3 d-inline-flex align-items-center gap-2">
                            <x-icon name="filter" size="14" /> تطبيق
                        </button>
                        <a href="{{ route('chain_plans.index') }}"
                            class="btn btn-outline-secondary px-4 fw-bold rounded-3 d-inline-flex align-items-center gap-2">
                            <x-icon name="x" size="14" /> إلغاء الكل
                        </a>
                    </div>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const govSelect = document.getElementById('filter-governorate');
                        const dirSelect = document.getElementById('filter-directorate');

                        function clearDirectorates() {
                            dirSelect.innerHTML = '<option value="">كل المديريات</option>';
                        }

                        async function loadDirectorates(governorateId, selectedId) {
                            clearDirectorates();
                            if (!governorateId) return;
                            try {
                                const res = await fetch(`/directorates/${governorateId}`);
                                if (!res.ok) return;
                                const data = await res.json();
                                data.forEach(d => {
                                    const opt = document.createElement('option');
                                    opt.value = d.id;
                                    opt.textContent = d.name;
                                    if (selectedId && selectedId == d.id) opt.selected = true;
                                    dirSelect.appendChild(opt);
                                });
                            } catch (e) {
                                console.error('Failed to load directorates', e);
                            }
                        }

                        govSelect && govSelect.addEventListener('change', function () {
                            loadDirectorates(this.value, null);
                        });

                        const initialGov = govSelect ? govSelect.value : null;
                        const initialDir = '{{ request('directorate_id') }}';
                        if (initialGov) {
                            loadDirectorates(initialGov, initialDir || null);
                        }
                    });
                </script>
            </x-slot>

            {{-- ========================================== --}}
            {{-- Table Slot --}}
            {{-- ========================================== --}}
            <x-slot name="table">
                <table class="table table-hover align-middle mb-0 border-top">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-muted text-center" style="width: 50px;">#</th>
                            <th>
                                <a href="{{ route('chain_plans.index', array_merge(request()->query(), ['sort' => 'governorate_id', 'direction' => (request('sort') == 'governorate_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none sortable-header d-flex align-items-center gap-1">
                                    المحافظة <x-icon name="chevron-down" size="14" class="icon" />
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('chain_plans.index', array_merge(request()->query(), ['sort' => 'directorate_id', 'direction' => (request('sort') == 'directorate_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none sortable-header d-flex align-items-center gap-1">
                                    المديرية <x-icon name="chevron-down" size="14" class="icon" />
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('chain_plans.index', array_merge(request()->query(), ['sort' => 'value_chain_id', 'direction' => (request('sort') == 'value_chain_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none sortable-header d-flex align-items-center gap-1">
                                    السلسلة <x-icon name="chevron-down" size="14" class="icon" />
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('chain_plans.index', array_merge(request()->query(), ['sort' => 'domain_id', 'direction' => (request('sort') == 'domain_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none sortable-header d-flex align-items-center gap-1">
                                    المجال <x-icon name="chevron-down" size="14" class="icon" />
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('chain_plans.index', array_merge(request()->query(), ['sort' => 'indicator', 'direction' => (request('sort') == 'indicator' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none sortable-header d-flex align-items-center gap-1">
                                    المؤشر <x-icon name="chevron-down" size="14" class="icon" />
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('chain_plans.index', array_merge(request()->query(), ['sort' => 'number', 'direction' => (request('sort') == 'number' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none sortable-header d-flex align-items-center gap-1">
                                    العدد <x-icon name="chevron-down" size="14" class="icon" />
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('chain_plans.index', array_merge(request()->query(), ['sort' => 'value_chain_financing_type_id', 'direction' => (request('sort') == 'value_chain_financing_type_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none sortable-header d-flex align-items-center gap-1">
                                    التمويل <x-icon name="chevron-down" size="14" class="icon" />
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('chain_plans.index', array_merge(request()->query(), ['sort' => 'funding_source_id', 'direction' => (request('sort') == 'funding_source_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none sortable-header d-flex align-items-center gap-1">
                                    مصدر التمويل <x-icon name="chevron-down" size="14" class="icon" />
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('chain_plans.index', array_merge(request()->query(), ['sort' => 'implementing_entity_id', 'direction' => (request('sort') == 'implementing_entity_id' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none sortable-header d-flex align-items-center gap-1">
                                    الجهة المنفذة <x-icon name="chevron-down" size="14" class="icon" />
                                </a>
                            </th>
                            <th class="text-center text-muted" style="width: 150px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @php
                            $formatAuthorities = function($value) {
                                if (empty($value) || $value === '-') return '-';
                                $ids = is_array($value) ? $value : (json_decode($value, true) ?: [$value]);
                                if (empty($ids)) return '-';
                                $names = \App\Models\Authority::whereIn('id', (array)$ids)->pluck('agency_name')->toArray();
                                return !empty($names) ? implode('، ', $names) : (is_array($value) ? implode('، ', $value) : $value);
                            };
                        @endphp
                        @forelse($chainPlans as $plan)
                            <tr>
                                <td class="text-muted fw-bold text-center">
                                    {{ ($chainPlans->currentPage() - 1) * $chainPlans->perPage() + $loop->iteration }}
                                </td>
                                <td class="fw-medium text-dark">{{ $plan->governorate->name ?? '-' }}</td>
                                <td>{{ $plan->directorate->name ?? '-' }}</td>
                                <td>{{ $plan->valueChain->name ?? $plan->value_chain_id ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">{{ $plan->domain->name ?? $plan->domain_id ?? '-' }}</span>
                                </td>
                                <td>{{ $plan->indicator }}</td>
                                <td class="fw-bold text-primary">{{ $plan->number }}</td>
                                <td>{{ $plan->financingType->name ?? $plan->value_chain_financing_type_id ?? '-' }}</td>
                                <td>{{ $formatAuthorities($plan->funding_source_id) }}</td>
                                <td>{{ $formatAuthorities($plan->authority_id ?: $plan->implementing_entity_id) }}</td>

                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        @can('chain_plans.view', $plan)
                                            <a href="{{ route('chain_plans.show', $plan->id) }}" class="btn-soft btn-soft-primary" title="عرض التفاصيل">
                                                <x-icon name="eye" size="16" />
                                            </a>
                                        @endcan

                                        @can('chain_plans.edit', $plan)
                                            <a href="{{ route('chain_plans.edit', $plan->id) }}" class="btn-soft btn-soft-success" title="تعديل">
                                                <x-icon name="edit-2" size="16" />
                                            </a>
                                        @endcan

                                        @can('chain_plans.print', $plan)
                                            <a href="{{ route('chain_plans.print', $plan->id) }}" target="_blank" class="btn-soft btn-soft-secondary" title="طباعة">
                                                <x-icon name="printer" size="16" />
                                            </a>
                                        @endcan

                                        @can('chain_plans.delete', $plan)
                                            <form action="{{ route('chain_plans.destroy', $plan->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-soft btn-soft-danger" title="حذف"
                                                    onclick="return confirm('هل أنت متأكد من رغبتك في حذف هذا السجل؟')">
                                                    <x-icon name="trash-2" size="16" />
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center opacity-75">
                                        <div class="mb-3 p-3 rounded-circle" style="background-color: #f8f9fa;">
                                            <x-icon name="inbox" size="40" class="text-secondary" />
                                        </div>
                                        <h6 class="text-muted fw-bold mb-1">لا توجد بيانات متاحة حالياً</h6>
                                        <p class="text-secondary small mb-0">لم يتم العثور على أي خطط مطابقة لبحثك.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-slot>

            {{-- ========================================== --}}
            {{-- Pagination Slots --}}
            {{-- ========================================== --}}
            <x-slot name="pagination">
                {{ $chainPlans->links() }}
            </x-slot>

            <x-slot name="total">
                {{ $chainPlans->total() }}
            </x-slot>

        </x-index-page>

@endsection