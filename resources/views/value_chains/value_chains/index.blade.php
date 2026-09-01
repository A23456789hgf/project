@extends('layouts.app')

@section('content')
    @include('configuration.shared_styles')
    <x-index-page title="سلاسل القيمة" icon="sitemap">

        <x-slot name="headerActions">

            @can('value_chains.create')
                <a href="{{ route('value-chains.create') }}" class="btn btn-primary auth-perm-value-chains-create">
                    <x-icon name="plus" size="14" />
                    إضافة سلسلة قيمة
                </a>
            @endcan

            {{-- 🔥 IMPORT BUTTON --}}
            @can('value_chains.import')
                <a href="{{ route('value-chains.import.form') }}" class="btn btn-success auth-perm-value-chains-import">
                    <x-icon name="upload" size="14" />
                    استيراد
                </a>
            @endcan

            {{-- 📄 TEMPLATE DOWNLOAD --}}
            @can('value_chains.import')
                <a href="{{ route('value-chains.import.template') }}" class="btn btn-outline-secondary">
                    <x-icon name="download" size="14" />
                    القالب
                </a>
            @endcan

        </x-slot>

        <x-slot name="table">
            <table class="table table-hover table-striped align-middle table-compact mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>الاسم</th>
                        <th>السلسلة الأم</th>
                        <th class="hide-lg">بواسطة</th>
                        <th class="text-center" width="180">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($valueChains as $item)
                        <tr>
                            <td>
                                <span class="fw-bold text-primary small">#{{ $item->id }}</span>
                            </td>

                            <td>
                                <div class="text-truncate-custom-lg small fw-medium" title="{{ $item->name }}">
                                    {{ $item->name }}
                                </div>
                            </td>

                            <td>
                                @if($item->parent)
                                    <span class="badge-compact badge-role">
                                        {{ $item->parent->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">ـ</span>
                                @endif
                            </td>

                            <td class="hide-lg">
                                @if($item->createdBy)
                                    <div class="creator-cell">
                                        <span class="creator-name" title="{{ $item->createdBy->name ?? '-' }}">
                                            {{ $item->createdBy->name ?? 'ـ' }}
                                        </span>
                                        <span class="creator-entity">
                                            {{ $item->createdBy->entity->name ?? 'ـ' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted small">ـ</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="btn-group" role="group">

                                    @can('series_financing.view')
                                        <a href="{{ route('value-chains.financing.index', $item) }}"
                                            class="btn btn-action-view btn-icon" title="جهات التمويل">
                                            <x-icon name="dollar-sign" class="action-icon" />
                                        </a>
                                    @endcan

                                    @can('participating_entities.view')
                                        <a href="{{ route('value-chains.participating-entities.index', $item) }}"
                                            class="btn btn-action-enable btn-icon" title="الجهات المشاركة">
                                            <x-icon name="users" class="action-icon" />
                                        </a>
                                    @endcan

                                    @can('value_chains.edit')
                                        <a href="{{ route('value-chains.edit', $item) }}"
                                            class="btn btn-action-edit btn-icon auth-perm-value-chains-edit" title="تعديل">
                                            <x-icon name="edit-2" class="action-icon" />
                                        </a>
                                    @endcan

                                    @can('value_chains.delete')
                                        <form action="{{ route('value-chains.destroy', $item) }}" method="POST" class="d-inline"
                                            onsubmit="return confirmAction(this, 'هل أنت متأكد من حذف سلسلة القيمة؟')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="btn btn-action-delete btn-icon auth-perm-value-chains-delete" title="حذف">
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
                                        <x-icon name="sitemap" size="40" class="text-muted" />
                                    </div>
                                    <div class="text-center">
                                        <h6 class="text-muted mb-1">لا توجد سلاسل قيمة حالياً</h6>
                                        <small class="text-muted d-block">يبدو أن القائمة فارغة.</small>
                                    </div>

                                    @can('value_chains.create')
                                        <a href="{{ route('value-chains.create') }}" class="btn btn-primary-compact btn-compact">
                                            <x-icon name="plus" size="12" /> إضافة سلسلة قيمة
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-slot>
        <!-- 
            <x-slot name="pagination">
                {{ $valueChains->links() }}
            </x-slot>

            <x-slot name="total">
                {{ $valueChains->total() }}
            </x-slot> -->

    </x-index-page>
@endsection