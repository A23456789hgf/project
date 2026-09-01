@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

<div class="container py-4">
    <x-ui.card title="المديريات" subtitle="إدارة واعتماد المديريات والمجموعات الجغرافية" icon="fas fa-map-marked-alt">
        <x-slot name="actions">
            <div class="d-flex gap-2">
                @can('directorates.create')
                    <x-ui.button href="{{ route('directorates.create') }}" variant="success" size="sm" icon="fas fa-plus">إضافة مديرية</x-ui.button>
                @endcan
                @can('directorates.export')
                    <x-ui.button href="{{ route('config.export', ['entity' => 'directorates']) }}" variant="outline-primary" size="sm" icon="fas fa-file-excel">تصدير Excel</x-ui.button>
                @endcan
                @can('directorates.import')
                    <x-ui.button href="{{ route('config.import', ['entity' => 'directorates']) }}" variant="outline-primary" size="sm" icon="fas fa-file-import">استيراد ملف</x-ui.button>
                @endcan
            </div>
        </x-slot>

        <form action="{{ route('directorates.index') }}" method="GET" class="row g-3 align-items-end mb-4 bg-light p-3 rounded-lg border">
            <div class="col-md-5">
                <x-ui.input name="search" label="البحث" placeholder="بحث في المديريات..." :value="request('search')" />
            </div>
            <div class="col-md-4">
                <x-ui.select name="governorate_id" label="المحافظة" placeholder="كل المحافظات" :selected="request('governorate_id')">
                    @foreach($governorates as $gov)
                        <option value="{{ $gov->id }}" {{ request('governorate_id') == $gov->id ? 'selected' : '' }}>{{ $gov->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <x-ui.button type="submit" variant="primary" icon="fas fa-filter" class="w-100">تصفية</x-ui.button>
                    <x-ui.button href="{{ route('directorates.index') }}" variant="secondary" icon="fas fa-undo" title="إعادة تعيين" />
                </div>
            </div>
        </form>

        <x-ui.table :headers="['#', 'المديرية', 'المحافظة', 'الإجراءات']">
            @forelse($directorates as $index => $item)
                <tr>
                    <td class="text-muted fw-bold">{{ $directorates->firstItem() + $index }}</td>
                    <td class="fw-bold text-dark">{{ $item->name }}</td>
                    <td>
                        <x-ui.badge variant="info" icon="fas fa-map-marker-alt">{{ $item->governorate->name }}</x-ui.badge>
                    </td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            @can('directorates.edit')
                                <x-ui.button href="{{ route('directorates.edit', $item) }}" variant="warning" size="sm" icon="fas fa-edit" title="تعديل" />
                            @endcan
                            @can('directorates.delete')
                                <form action="{{ route('directorates.destroy', $item) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" size="sm" icon="fas fa-trash-alt" title="حذف" onclick="return confirmAction(this, 'هل تريد الحذف؟')" />
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <x-ui.empty-state title="لا توجد مديريات" subtitle="لم يتم العثور على أي مديرية مطابقة للبحث." />
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        <div class="mt-3">
            {{ $directorates->links() }}
        </div>
    </x-ui.card>
</div>
@endsection
