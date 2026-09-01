@extends('layouts.app')

@section('content')
    @include('configuration.shared_styles')

    <link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

    <div class="container py-4">
        <x-ui.card title="الجهات الممولة" subtitle="إدارة واعتماد الجهات والمنظمات الممولة للمشاريع" icon="fas fa-building">
            <x-slot name="actions">
                @can('funded-entities.create')
                    <x-ui.button href="{{ route('funded-entities.create') }}" variant="success" size="sm" icon="fas fa-plus">إضافة جهة ممولة</x-ui.button>
                @endcan
            </x-slot>

            <x-ui.table :headers="['#', 'اسم الجهة الممولة', 'مصدر التمويل', 'الإجراءات']">
                @forelse($fundedEntities as $entity)
                    <tr>
                        <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td class="fw-bold text-dark">{{ $entity->name }}</td>
                        <td>
                            <x-ui.badge variant="info" icon="fas fa-money-bill-wave">{{ $entity->fundingSource->name ?? 'غير محدد' }}</x-ui.badge>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                @can('funded-entities.edit')
                                    <x-ui.button href="{{ route('funded-entities.edit', $entity->id) }}" variant="warning" size="sm" icon="fas fa-edit" title="تعديل" />
                                @endcan
                                @can('funded-entities.delete')
                                    <form action="{{ route('funded-entities.destroy', $entity->id) }}" method="POST" class="d-inline">
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
                            <x-ui.empty-state title="لا توجد جهات ممولة" subtitle="لم يتم تسجيل أي جهة ممولة حالياً." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>

            <div class="mt-3">
                {{ $fundedEntities->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection