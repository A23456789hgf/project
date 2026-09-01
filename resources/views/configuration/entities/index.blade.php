@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

<div class="container py-4">
    <x-ui.card title="قائمة الجهات" subtitle="تتبع وإدارة الجهات الإدارية والمؤسسية" icon="fas fa-hand-holding-usd">
        <x-slot name="actions">
            <form action="{{ route('entities.index') }}" method="GET" class="d-flex gap-2">
                <x-ui.input name="search" placeholder="بحث في الجهات..." :value="request('search')" style="width: 280px;" />
                <x-ui.button type="submit" variant="primary" icon="fas fa-search">بحث</x-ui.button>
            </form>
        </x-slot>

        <x-ui.table :headers="['#', 'اسم الجهة', 'اسم الأب']">
            @forelse($entities as $index => $entity)
                <tr>
                    <td class="text-muted fw-bold">{{ $index + 1 }}</td>
                    <td class="fw-bold text-dark">{{ $entity['entity_name'] }}</td>
                    <td class="text-muted">{{ $entity['father_name'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-4">
                        <x-ui.empty-state title="لا توجد جهات" subtitle="لم يتم العثور على أي جهة مطابقة للبحث." />
                    </td>
                </tr>
            @endforelse
        </x-ui.table>
    </x-ui.card>
</div>
@endsection
