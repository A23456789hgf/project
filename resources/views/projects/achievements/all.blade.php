@extends('layouts.app')

@section('styles')
<style>
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        font-size: 0.85rem;
    }
    .text-truncate-custom-lg {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endsection

@section('content')
    <x-index-page title="إنجازات المشاريع السابقة" icon="trophy" :paginator="$achievements">

        <x-slot name="breadcrumb">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:0.82rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none text-muted">المشاريع</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">إنجازات المشاريع السابقة</li>
                </ol>
            </nav>
        </x-slot>

        <x-slot name="filters">
            {{-- Dropdown selector to add new achievement --}}
            <div class="row align-items-end g-3 mb-4">
                <div class="col-md-9">
                    <label for="select-project-ach" class="form-label small text-muted fw-bold">اختر مشروعاً سابقاً لعرض فرز إنجازاته أو إضافة إنجاز له:</label>
                    <select id="select-project-ach" class="form-select select2-enable">
                        <option value="">-- عرض إنجازات كافة المشاريع --</option>
                        @foreach($oldProjects as $op)
                            <option value="{{ $op->id }}" data-url="{{ route('projects.achievements.create', $op->id) }}" {{ request('project_id') == $op->id ? 'selected' : '' }}>
                                {{ $op->project_name }} ({{ $op->form_number ?? 'لا يوجد رقم' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <a id="btn-add-ach-redirect" href="#" class="btn w-100 fw-bold d-none" style="height: 38px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #c9a961, #a8843f); color: white; border: none; border-radius: 8px;">
                        <i class="fas fa-plus-circle me-2"></i> تسجيل إنجاز للمشروع
                    </a>
                </div>
            </div>

            {{-- Search Bar --}}
            <form action="{{ route('projects.achievements.all') }}" method="GET" class="row g-2">
                @if(request()->filled('project_id'))
                    <input type="hidden" name="project_id" value="{{ request('project_id') }}">
                @endif
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="ابحث باسم المشروع، رقم المشروع، أو نوع التقرير..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius: 8px; height: 38px;">بحث</button>
                </div>
            </form>
        </x-slot>

        <x-slot name="table">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>رقم المشروع</th>
                        <th class="text-start">اسم المشروع</th>
                        <th>نوع التقرير</th>
                        <th>الفترة الزمنية</th>
                        <th>نسبة الإنجاز</th>
                        <th>المستفيدين</th>
                        <th>تاريخ الإضافة</th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($achievements as $index => $achievement)
                        @php
                            // التحقق من وجود المشروع قبل استخدامه
                            $project = $achievement->project;
                            $hasProject = !is_null($project);
                            $pct = (float)($achievement->new_achievement ?? 0);
                        @endphp
                        <tr>
                            <td class="text-muted fw-bold">{{ $achievements->firstItem() + $index }}</td>
                            <td>
                                @if($hasProject)
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                        {{ $project->form_number ?? 'ـ' }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        مشروع محذوف
                                    </span>
                                @endif
                            </td>
                            <td class="text-start fw-medium text-dark">
                                @if($hasProject)
                                    <div class="fw-bold text-truncate-custom-lg" style="max-width: 320px;" title="{{ $project->project_name ?? '' }}">
                                        {{ $project->project_name ?? 'غير معروف' }}
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">المشروع غير موجود</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                    {{ optional($achievement->reportType)->name ?? 'ـ' }}
                                </span>
                            </td>
                            <td>
                                <span class="small text-muted">
                                    {{ $achievement->start_date_gregorian?->format('Y/m/d') ?? 'ـ' }}
                                    <i class="fas fa-long-arrow-alt-left mx-1 text-gold" style="color:#c9a961;"></i>
                                    {{ $achievement->end_date_gregorian?->format('Y/m/d') ?? 'ـ' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold">
                                    {{ number_format($pct, 1) }}%
                                </span>
                            </td>
                            <td class="fw-bold text-primary">{{ number_format($achievement->number_of_beneficiaries ?? 0) }}</td>
                            <td class="small text-muted">{{ $achievement->created_at?->format('Y/m/d') ?? 'ـ' }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    @if($hasProject)
                                        <a href="{{ route('projects.achievements.show', [$project->id, $achievement->id]) }}" 
                                           class="btn btn-action btn-primary" title="عرض التفاصيل">
                                            <i class="fas fa-eye text-white"></i>
                                        </a>
                                        <a href="{{ route('projects.achievements.print-single', [$project->id, $achievement->id]) }}" 
                                           target="_blank" class="btn btn-action btn-outline-secondary" title="طباعة">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">غير متاح</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3" style="color:#c9a961;"></i>
                                <h5>لا توجد سجلات إنجازات مضافة بعد</h5>
                                <p class="small">قم باختيار مشروع من القائمة المنسدلة لإضافة إنجاز جديد</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-slot>

    </x-index-page>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectEl = document.getElementById('select-project-ach');
        const btnEl = document.getElementById('btn-add-ach-redirect');

        if (selectEl && btnEl) {
            // Apply select2 if loaded
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                jQuery(selectEl).select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                }).on('change', function() {
                    handleSelectionChange();
                });
            } else {
                selectEl.addEventListener('change', function() {
                    handleSelectionChange();
                });
            }

            // Call initially to show/hide button if request contains project_id
            updateButton();

            function handleSelectionChange() {
                const projectId = selectEl.value;
                const urlParams = new URLSearchParams(window.location.search);
                
                if (projectId) {
                    urlParams.set('project_id', projectId);
                } else {
                    urlParams.delete('project_id');
                }
                
                // Keep search query if present
                window.location.href = "{{ route('projects.achievements.all') }}?" + urlParams.toString();
            }

            function updateButton() {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const url = selectedOption ? selectedOption.getAttribute('data-url') : null;
                
                if (url && selectEl.value !== "") {
                    btnEl.setAttribute('href', url);
                    btnEl.classList.remove('d-none');
                } else {
                    btnEl.classList.add('d-none');
                }
            }

            // Listen for select2 change events
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                jQuery(selectEl).on('select2:select', function(e) {
                    updateButton();
                });
            }
        }
    });
</script>
@endsection