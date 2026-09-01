@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

<div class="container py-4">
    <x-ui.card title="المحافظات" subtitle="إدارة واعتماد المحافظات والنطاق الجغرافي" icon="fas fa-map-marked-alt">
        <x-slot name="actions">
            <div class="d-flex gap-2">
                @can('governorates.create')
                    <x-ui.button href="{{ route('governorates.create') }}" variant="success" size="sm" icon="fas fa-plus">إضافة محافظة</x-ui.button>
                @endcan
                @can('governorates.export')
                    <x-ui.button href="{{ route('config.export', ['entity' => 'governorates']) }}" variant="outline-primary" size="sm" icon="fas fa-file-excel">تصدير Excel</x-ui.button>
                @endcan
                @can('governorates.import')
                    <x-ui.button href="{{ route('config.import', ['entity' => 'governorates']) }}" variant="outline-primary" size="sm" icon="fas fa-file-import">استيراد ملف</x-ui.button>
                @endcan
            </div>
        </x-slot>

        <!-- Search/Filter Section -->
        <form method="GET" class="row g-3 align-items-end mb-4 bg-light p-3 rounded-lg border">
            <div class="col-md-6">
                <x-ui.input name="search" label="البحث" placeholder="بحث في المحافظات..." :value="request('search')" />
            </div>
            <div class="col-md-3">
                <x-ui.select name="per_page" label="عدد النتائج" :selected="request('per_page', 20)" onchange="this.form.submit()">
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                    <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                    <option value="500" {{ request('per_page', 20) == 500 ? 'selected' : '' }}>500</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.button type="submit" variant="primary" icon="fas fa-search" class="w-100">بحث</x-ui.button>
            </div>
        </form>

        <x-ui.table :headers="['#', 'اسم المحافظة', 'الإجراءات']">
            @forelse($governorates as $index => $governorate)
                <tr>
                    <td class="text-muted fw-bold">{{ $governorates->firstItem() + $index }}</td>
                    <td class="fw-bold text-dark">{{ $governorate->name }}</td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            @can('governorates.edit')
                                <x-ui.button href="{{ route('governorates.edit', $governorate) }}" variant="warning" size="sm" icon="fas fa-edit" title="تعديل" />
                            @endcan
                            @can('governorates.delete')
                                <form action="{{ route('governorates.destroy', $governorate) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" size="sm" icon="fas fa-trash-alt" title="حذف" onclick="return confirmAction(this, 'هل تريد الحذف؟')" />
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-4">
                        <x-ui.empty-state title="لا توجد محافظات" subtitle="لم يتم العثور على أي محافظة مطابقة للبحث." />
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        <div class="mt-3">
            {{ $governorates->links() }}
        </div>
    </x-ui.card>
</div>
@endsection
