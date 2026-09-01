@extends('layouts.app')

@section('content')
    <x-index-page title="الجهات المشاركة - {{ $valueChain->name }}" icon="users">

        <x-slot name="headerActions">
            <a href="{{ route('value-chains.index') }}" class="btn btn-secondary me-2">
                <x-icon name="arrow-right" size="14" />
                عودة للسلاسل
            </a>
            @canany(['value-chains.edit','participating_entities.create','participating-entities.create'])
                <a href="{{ route('value-chains.participating-entities.create', $valueChain) }}" class="btn btn-primary">
                    <x-icon name="plus" size="14" />
                    إضافة جهة مشاركة
                </a>
            @endcanany
        </x-slot>

        <x-slot name="table">
            <table class="table table-hover table-striped align-middle table-compact mb-0">
                <thead>
                    <tr>
                        <th width="80">#</th>
                        <th>نوع الجهة</th>
                        <th>اسم الجهة</th>
                        <th class="hide-lg">بواسطة</th>
                        <th class="text-center" width="120">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entities as $item)
                        <tr>
                            <td>
                                <span class="fw-bold text-primary small">{{ $loop->iteration }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $item->entity_type == 'internal' ? 'bg-info' : 'bg-secondary' }}">
                                    {{ $item->entity_type == 'internal' ? 'داخلية' : 'خارجية' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-medium text-dark small">{{ $item->entity_name }}</div>
                            </td>
                            <td class="hide-lg">
                                @if($item->creator)
                                    <div class="creator-cell">
                                        <span class="creator-name" title="{{ $item->creator->name ?? '-' }}">
                                            {{ $item->creator->name ?? 'ـ' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted small">ـ</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    @can('participating_entities.edit')
                                        <a href="{{ route('value-chains.participating-entities.edit', [$valueChain, $item]) }}"
                                            class="btn btn-action-edit btn-icon" title="تعديل">
                                            <x-icon name="edit-2" class="action-icon" />
                                        </a>
                                    @endcan

                                    @can('participating_entities.delete')
                                        <form action="{{ route('value-chains.participating-entities.destroy', [$valueChain, $item]) }}" method="POST" class="d-inline"
                                            onsubmit="return confirmAction(this, 'هل أنت متأكد من حذف الجهة المشاركة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-action-delete btn-icon" title="حذف">
                                                <x-icon name="trash-2" class="action-icon" />
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="d-flex flex-column align-items-center gap-3">
                                    <div class="p-4 rounded-circle bg-light">
                                        <x-icon name="users" size="40" class="text-muted" />
                                    </div>
                                    <div class="text-center">
                                        <h6 class="text-muted mb-1">لا توجد جهات مشاركة حالياً</h6>
                                        <small class="text-muted d-block">يبدو أن القائمة فارغة.</small>
                                    </div>
                                    @can('value-chains.edit')
                                        <a href="{{ route('value-chains.participating-entities.create', $valueChain) }}" class="btn btn-primary-compact btn-compact">
                                            <x-icon name="plus" size="12" /> إضافة جهة مشاركة
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-slot>

    </x-index-page>
@endsection
