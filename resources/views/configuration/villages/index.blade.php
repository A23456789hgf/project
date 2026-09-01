@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

<div class="container py-4">
    <x-ui.card title="إدارة القرى" subtitle="تتبع وإدارة القرى والمناطق الميدانية" icon="fas fa-home">
        <x-slot name="actions">
            <div class="d-flex gap-2">
                @can('villages.create')
                    <x-ui.button href="{{ route('villages.create') }}" variant="success" size="sm" icon="fas fa-plus">إضافة قرية</x-ui.button>
                @endcan
                @can('villages.export')
                    <x-ui.button href="{{ route('config.export', ['entity' => 'villages']) }}" variant="outline-primary" size="sm" icon="fas fa-file-excel">تصدير Excel</x-ui.button>
                @endcan
                @can('villages.import')
                    <x-ui.button href="{{ route('config.import', ['entity' => 'villages']) }}" variant="outline-primary" size="sm" icon="fas fa-file-import">استيراد ملف</x-ui.button>
                @endcan
            </div>
        </x-slot>

        <form action="{{ route('villages.index') }}" method="GET" class="row g-3 align-items-end mb-4 bg-light p-3 rounded-lg border">
            <div class="col-md-3">
                <x-ui.input name="search" label="البحث" placeholder="بحث في القرى..." :value="request('search')" />
            </div>
            <div class="col-md-2">
                <x-ui.select name="governorate_id" label="المحافظة" placeholder="جميع المحافظات" :selected="request('governorate_id')" id="governorate_id">
                    @foreach($governorates as $governorate)
                        <option value="{{ $governorate->id }}" {{ request('governorate_id') == $governorate->id ? 'selected' : '' }}>{{ $governorate->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-2">
                <x-ui.select name="directorate_id" label="المديرية" placeholder="جميع المديريات" :selected="request('directorate_id')" id="directorate_id">
                    @foreach($directorates as $directorate)
                        <option value="{{ $directorate->id }}" {{ request('directorate_id') == $directorate->id ? 'selected' : '' }}>{{ $directorate->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-2">
                <x-ui.select name="sub_area_id" label="العزلة/المنطقة" placeholder="جميع العزل" :selected="request('sub_area_id')" id="sub_area_id">
                    @foreach($subAreas as $subArea)
                        <option value="{{ $subArea->id }}" {{ request('sub_area_id') == $subArea->id ? 'selected' : '' }}>{{ $subArea->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <x-ui.button type="submit" variant="primary" icon="fas fa-filter" class="w-100">تصفية</x-ui.button>
                    <x-ui.button href="{{ route('villages.index') }}" variant="secondary" icon="fas fa-redo" title="إعادة تعيين" />
                </div>
            </div>
        </form>

        <x-ui.table :headers="['#', 'اسم القرية', 'المحافظة', 'المديرية', 'العزلة/المنطقة', 'الإجراءات']">
            @forelse ($villages as $village)
                <tr>
                    <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                    <td class="fw-bold text-dark">{{ $village->name }}</td>
                    <td><x-ui.badge variant="info" icon="fas fa-map-marker-alt">{{ $village->governorate->name ?? '-' }}</x-ui.badge></td>
                    <td>{{ $village->directorate->name ?? '-' }}</td>
                    <td>{{ $village->subArea->name ?? '-' }}</td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            @can('villages.edit')
                                <x-ui.button href="{{ route('villages.edit', $village) }}" variant="warning" size="sm" icon="fas fa-edit" title="تعديل" />
                            @endcan
                            @can('villages.delete')
                                <form action="{{ route('villages.destroy', $village) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" size="sm" icon="fas fa-trash-alt" title="حذف" onclick="return confirmAction(this, 'هل تريد الحذف؟')" />
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <x-ui.empty-state title="لا توجد قرى" subtitle="لم يتم العثور على أي قرية مطابقة للبحث." />
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        <div class="mt-3">
            {{ $villages->links() }}
        </div>
    </x-ui.card>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#governorate_id').change(function() {
            var govId = $(this).val();
            $('#directorate_id').html('<option value="">جاري التحميل...</option>');
            $('#sub_area_id').html('<option value="">اختر المديرية أولاً</option>');
            if (govId) {
                $.get('/api/locations/directorates/' + govId, function(data) {
                    $('#directorate_id').html('<option value="">جميع المديريات</option>');
                    $.each(data, function(k, v) {
                        $('#directorate_id').append('<option value="'+ v.id +'">'+ v.name +'</option>');
                    });
                });
            } else {
                $('#directorate_id').html('<option value="">جميع المديريات</option>');
                $('#sub_area_id').html('<option value="">جميع العزل</option>');
            }
        });

        $('#directorate_id').change(function() {
            var dirId = $(this).val();
            $('#sub_area_id').html('<option value="">جاري التحميل...</option>');
            if (dirId) {
                var govId = $('#governorate_id').val() || '0';
                $.get('/api/locations/sub-areas/' + govId + '/' + dirId, function(data) {
                    $('#sub_area_id').html('<option value="">جميع العزل</option>');
                    $.each(data, function(k, v) {
                        $('#sub_area_id').append('<option value="'+ v.id +'">'+ v.name +'</option>');
                    });
                });
            } else {
                $('#sub_area_id').html('<option value="">جميع العزل</option>');
            }
        });

        $('#per_page').on('change', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('per_page', $(this).val());
            window.location.href = window.location.pathname + '?' + params.toString();
        });
    });
</script>
@endsection