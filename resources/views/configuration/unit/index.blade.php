@extends('layouts.app')

@section('title', 'إدارة الوحدات')

@section('content')
@include('configuration.shared_styles')

<link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

<div class="container py-4">
    <x-ui.card title="إدارة الوحدات" subtitle="قائمة وحدات القياس المعتمدة والمعرفة بالنظام" icon="fas fa-cubes">
        <x-slot name="actions">
            <div class="d-flex gap-2">
                @can('units.create')
                    <x-ui.button href="{{ route('units.create') }}" variant="success" size="sm" icon="fas fa-plus">إضافة وحدة</x-ui.button>
                @endcan
                @can('units.export')
                    <x-ui.button href="{{ route('config.export', ['entity' => 'units']) }}" variant="outline-primary" size="sm" icon="fas fa-file-export">تصدير</x-ui.button>
                @endcan
                @can('units.import')
                    <x-ui.button href="{{ route('config.import', ['entity' => 'units']) }}" variant="outline-primary" size="sm" icon="fas fa-file-import">استيراد</x-ui.button>
                @endcan
            </div>
        </x-slot>

        <x-ui.table :headers="['#', 'اسم الوحدة', 'الحالة']" id="unitsTable">
            @foreach($units as $unit)
                @php
                    $unitObj = is_array($unit) ? (object)$unit : $unit;
                    $displayName = isset($unitObj->display_name) ? $unitObj->display_name : '';
                    $originalName = $unitObj->unit_name ?? $unitObj->name ?? '';
                @endphp
                <tr>
                    <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                    <td class="text-start">
                        @if($displayName && $displayName !== $originalName)
                            <div class="fw-bold text-dark">{{ $displayName }}</div>
                            <div class="text-muted small"><i class="fas fa-link me-1 opacity-50"></i> {{ $originalName }}</div>
                        @else
                            <div class="fw-bold text-dark">{{ $originalName }}</div>
                        @endif
                    </td>
                    <td>
                        <x-ui.badge variant="warning" icon="fas fa-eye">عرض فقط</x-ui.badge>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.card>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        font-size: 0.85rem !important;
        padding: 1rem 0;
    }
    .dataTables_filter input {
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        padding: 0.4rem 1rem;
        background: #f8f9fa;
    }
    .dataTables_length select {
        border-radius: 0.4rem;
        border: 1px solid #dee2e6;
        padding: 0.25rem 2rem 0.25rem 0.75rem;
    }
    .pagination .page-link {
        color: #001f3f;
    }
    .pagination .active .page-link {
        background-color: #001f3f;
        border-color: #001f3f;
        color: #D4AF37;
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        const table = $('#unitsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json',
                lengthMenu: "عرض _MENU_ سجلات",
                info: "عرض سجل رقم _START_ إلى _END_ من إجمالي _TOTAL_ وحدة",
                infoEmpty: "لا توجد سجلات متاحة",
                infoFiltered: "(تصفية من إجمالي _MAX_ سجل)",
                search: "بحث سريع:",
                zeroRecords: "لم يتم العثور على أي وحدات مطابقة",
                paginate: {
                    first: "الأول",
                    previous: "السابق",
                    next: "التالي",
                    last: "الأخير"
                }
            },
            order: [[0, 'asc']],
            pageLength: 20,
            lengthMenu: [
                [20, 50, 100, 500],
                ['٢٠ وحدة', '٥٠ وحدة', '١٠٠ وحدة', '٥٠٠ وحدة']
            ],
            dom: '<"row align-items-center"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            drawCallback: function() {
                updateArabicNumbers();
            }
        });

        function updateArabicNumbers() {
            $('.dataTables_length select option').each(function() {
                var text = $(this).text();
                var match = text.match(/\d+/);
                if (match) {
                    var num = match[0];
                    $(this).text(text.replace(num, toArabicNumbers(num)));
                }
            });

            $('.dataTables_info, .pagination .page-link, #unitsTable tbody tr td:first-child').each(function() {
                var text = $(this).text();
                var numbers = text.match(/\d+/g);
                if (numbers) {
                    var self = this;
                    numbers.forEach(function(num) {
                        text = text.replace(num, toArabicNumbers(num));
                    });
                    $(self).text(text);
                }
            });
        }

        function toArabicNumbers(num) {
            var arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            return num.toString().replace(/\d/g, function(d) {
                return arabicNumbers[d];
            });
        }
    });
</script>
@endsection