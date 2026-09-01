@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

<div class="container py-4">
    <x-ui.card title="إدارة العزل والمناطق" subtitle="عرض وعزل المناطق والمجموعات الفرعية الجغرافية" icon="fas fa-map-marker-alt">
        <x-slot name="actions">
            <div class="d-flex gap-2">
                @can('sub-areas.create')
                    <x-ui.button href="{{ route('sub-areas.create') }}" variant="success" size="sm" icon="fas fa-plus">إضافة عزلة</x-ui.button>
                @endcan
                @can('sub-areas.export')
                    <x-ui.button href="{{ route('config.export', ['entity' => 'sub-areas']) }}" variant="outline-primary" size="sm" icon="fas fa-file-excel">تصدير Excel</x-ui.button>
                @endcan
                @can('sub-areas.import')
                    <x-ui.button href="{{ route('config.import', ['entity' => 'sub-areas']) }}" variant="outline-primary" size="sm" icon="fas fa-file-import">استيراد ملف</x-ui.button>
                @endcan
            </div>
        </x-slot>

        <form action="{{ route('sub-areas.index') }}" method="GET" class="row g-3 align-items-end mb-4 bg-light p-3 rounded-lg border">
            <div class="col-md-4">
                <x-ui.input name="search" label="البحث" placeholder="اسم العزلة أو المحافظة..." :value="request('search')" />
            </div>
            <div class="col-md-3">
                <x-ui.select name="governorate_id" label="المحافظة" placeholder="جميع المحافظات" :selected="request('governorate_id')" id="governorateSelect">
                    @foreach($governorates as $gov)
                        <option value="{{ $gov->id }}" {{ request('governorate_id') == $gov->id ? 'selected' : '' }}>{{ $gov->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select name="directorate_id" label="المديرية" placeholder="جميع المديريات" :selected="request('directorate_id')" id="directorateSelect">
                    @foreach($directorates as $dir)
                        <option value="{{ $dir->id }}" {{ request('directorate_id') == $dir->id ? 'selected' : '' }}>{{ $dir->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-2">
                <x-ui.button type="submit" variant="primary" icon="fas fa-filter" class="w-100">تصفية</x-ui.button>
            </div>
        </form>

        <x-ui.table :headers="['اسم العزلة', 'المديرية', 'المحافظة', 'الإجراءات']">
            @forelse($subAreas as $subArea)
                <tr>
                    <td class="fw-bold text-dark">{{ $subArea->name }}</td>
                    <td>{{ $subArea->directorate->name ?? 'غير محدد' }}</td>
                    <td>
                        <x-ui.badge variant="info" icon="fas fa-map-marker-alt">{{ $subArea->governorate->name ?? 'غير محدد' }}</x-ui.badge>
                    </td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            @can('sub-areas.edit')
                                <x-ui.button href="{{ route('sub-areas.edit', $subArea) }}" variant="warning" size="sm" icon="fas fa-edit" title="تعديل" />
                            @endcan
                            @can('sub-areas.delete')
                                <form action="{{ route('sub-areas.destroy', $subArea) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <x-ui.empty-state title="لا توجد عزل" subtitle="لم يتم العثور على أي عزلة مطابقة للبحث." />
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        <div class="mt-3">
            {{ $subAreas->links() }}
        </div>
    </x-ui.card>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const governorateSelect = document.getElementById('governorateSelect');
        const directorateSelect = document.getElementById('directorateSelect');

        governorateSelect.addEventListener('change', function() {
            const governorateId = this.value;
            if (!governorateId) {
                directorateSelect.innerHTML = '<option value="">جميع المديريات</option>';
                return;
            }
            fetch(`/api/locations/directorates/${governorateId}`)
                .then(response => response.json())
                .then(data => {
                    directorateSelect.innerHTML = '<option value="">جميع المديريات</option>';
                    data.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.name;
                        directorateSelect.appendChild(opt);
                    });
                });
        });

        const perPageSelect = document.getElementById('per_page');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                const params = new URLSearchParams(window.location.search);
                params.set('per_page', this.value);
                window.location.href = window.location.pathname + '?' + params.toString();
            });
        }
    });
</script>
@endsection
