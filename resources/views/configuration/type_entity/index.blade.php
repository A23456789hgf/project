@extends('layouts.app')

@section('title', 'أنواع الجهات')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

    <div class="container py-4">
        <x-ui.card title="أنواع الجهات" subtitle="تعريف وتصنيف أنواع الجهات في النظام" icon="fas fa-sitemap">
            <x-slot name="actions">
                @can('type_entity.create')
                    <x-ui.button href="{{ route('type-entity.create') }}" variant="success" size="sm" icon="fas fa-plus">إضافة نوع جهة</x-ui.button>
                @endcan
            </x-slot>

            <div class="mb-4 bg-light p-3 rounded-lg border">
                <x-ui.input name="search" id="search" label="البحث" placeholder="بحث في أنواع الجهات..." />
            </div>

            <x-ui.table :headers="['#', 'نوع الجهة', 'الحالة', 'الإجراءات']" id="typeEntityTable">
                <tbody id="tableBody">
                    @forelse($types as $index => $item)
                        <tr>
                            <td class="text-muted fw-bold">{{ $index + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $item->name }}</td>
                            <td>
                                @if($item->is_active)
                                    <x-ui.badge variant="success" icon="fas fa-check">مفعل</x-ui.badge>
                                @else
                                    <x-ui.badge variant="secondary" icon="fas fa-times">غير مفعل</x-ui.badge>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @can('type_entity.edit')
                                        <x-ui.button href="{{ route('type-entity.edit', $item->id) }}" variant="warning" size="sm" icon="fas fa-edit" title="تعديل" />
                                    @endcan
                                    @can('type_entity.delete')
                                        <form action="{{ route('type-entity.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <x-ui.button type="submit" variant="danger" size="sm" icon="fas fa-trash-alt" title="حذف" onclick="return confirmAction(this, 'هل تريد حذف نوع الجهة؟')" />
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                لا توجد بيانات متاحة حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.card>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('search').addEventListener('input', e => {
            const searchTerm = e.target.value.toLowerCase();

            document.querySelectorAll('#tableBody tr').forEach(row => {
                const text = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
@endsection