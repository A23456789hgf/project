@extends('layouts.app')

@section('content')
    <x-index-page title="أعضاء سلاسل القيمة" icon="users">
        <form method="GET" action="{{ route('value-chain-members.index') }}" class="mb-3">

            <div class="row g-2">

                {{-- السلسلة --}}
                <div class="col-md-3">
                    <select name="value_chain_id" class="form-control form-control-sm">
                        <option value="">كل السلاسل</option>
                        @foreach($valueChains as $chain)
                            <option value="{{ $chain->id }}" {{ request('value_chain_id') == $chain->id ? 'selected' : '' }}>
                                {{ $chain->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- الدور --}}
                <div class="col-md-3">
                    <select name="role" class="form-control form-control-sm">
                        <option value="">كل الأدوار</option>
                        <option value="program_manager" {{ request('role') == 'program_manager' ? 'selected' : '' }}>
                            مدير البرنامج
                        </option>
                        <option value="chain_officer" {{ request('role') == 'chain_officer' ? 'selected' : '' }}>
                            ضابط سلسلة
                        </option>
                        <option value="coordinator" {{ request('role') == 'coordinator' ? 'selected' : '' }}>
                            منسق
                        </option>
                    </select>
                </div>

                {{-- المحافظة --}}
                <div class="col-md-3">
                    <select name="governorate_id" id="governorate" class="form-control form-control-sm">
                        <option value="">كل المحافظات</option>
                        @foreach($governorates as $gov)
                            <option value="{{ $gov->id }}" {{ request('governorate_id') == $gov->id ? 'selected' : '' }}>
                                {{ $gov->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- المديرية --}}
                <div class="col-md-3">
                    <select name="directorate_id" id="directorate" class="form-control form-control-sm">
                        <option value="">كل المديريات</option>
                        @foreach($directorates as $dir)
                            <option value="{{ $dir->id }}" {{ request('directorate_id') == $dir->id ? 'selected' : '' }}>
                                {{ $dir->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="mt-2 d-flex gap-2">

                <button class="btn btn-primary btn-sm">
                    <x-icon name="filter" size="12" />
                    فرز
                </button>

                <a href="{{ route('value-chain-members.index') }}" class="btn btn-secondary btn-sm">
                    إعادة تعيين
                </a>

            </div>

        </form>
        <x-slot name="headerActions">
            @can('value_chain_members.create')
                <a href="{{ route('value-chain-members.create') }}"
                    class="btn btn-primary auth-perm-value-chain-members-create">
                    <x-icon name="plus" size="14" />
                    إضافة عضو
                </a>
            @endcan
        </x-slot>

        <x-slot name="table">
            <table class="table table-hover table-striped align-middle table-compact mb-0">
                <thead>
                    <tr>
                        <th width="80">#</th>
                        <th>اسم الموظف</th>
                        <th>الدور</th>
                        <th>السلسلة</th>
                        <th>المحافظة </th>
                        <th> المديرية</th>
                        <th>الهاتف</th>
                        <th>الحالة</th>
                        <th class="hide-lg">بواسطة</th>
                        <th class="text-center" width="180">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $item)
                        <tr>
                            <td>
                                <span
                                    class="fw-bold text-primary small">#{{ $loop->iteration + ($members->currentPage() - 1) * $members->perPage() }}</span>
                            </td>
                            <td>
                                <div class="fw-medium text-dark small">{{ $item->name }}</div>
                            </td>
                            <td>
                                <span class="badge-compact badge-role">
                                    @if($item->role == 'program_manager')
                                        مدير البرنامج
                                    @elseif($item->role == 'chain_officer')
                                        ضابط سلسلة
                                    @else
                                        منسق
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div class="fw-medium small">{{ $item->valueChain->name ?? 'ـ' }}</div>
                            <td>
                                <div class="small">
                                    {{ $item->governorate->name ?? 'ـ'}}
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    {{ $item->directorate->name ?? 'ـ' }}
                                </div>
                            </td>
                            </td>
                            <td>
                                <div class="small">{{ $item->phone ?? 'ـ' }}</div>
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success-subtle text-success">مفعل</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">معطل</span>
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
                                    @can('value_chain_members.edit')
                                        <a href="{{ route('value-chain-members.edit', $item) }}"
                                            class="btn btn-action-edit btn-icon auth-perm-value-chain-members-edit" title="تعديل">
                                            <x-icon name="edit-2" class="action-icon" />
                                        </a>
                                    @endcan

                                    @can('value_chain_members.delete')
                                        <form action="{{ route('value-chain-members.destroy', $item) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirmAction(this, 'هل أنت متأكد من حذف هذا العضو؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-action-delete btn-icon auth-perm-value-chain-members-delete"
                                                title="حذف">
                                                <x-icon name="trash-2" class="action-icon" />
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="d-flex flex-column align-items-center gap-3">
                                    <div class="p-4 rounded-circle bg-light">
                                        <x-icon name="users" size="40" class="text-muted" />
                                    </div>
                                    <div class="text-center">
                                        <h6 class="text-muted mb-1">لا يوجد أعضاء حالياً</h6>
                                        <small class="text-muted d-block">يبدو أن القائمة فارغة.</small>
                                    </div>
                                    @can('value_chain_members.create')
                                        <a href="{{ route('value-chain-members.create') }}"
                                            class="btn btn-primary-compact btn-compact">
                                            <x-icon name="plus" size="12" /> إضافة عضو
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-slot>

        <x-slot name="pagination">
            {{ $members->links() }}
        </x-slot>

        <x-slot name="total">
            {{ $members->total() }}
        </x-slot>

    </x-index-page>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#governorate').on('change', function () {
                let govId = $(this).val();
                
                $('#directorate').html('<option value="">جاري التحميل...</option>');

                if (govId) {
                    $.ajax({
                        url: '/directorates/by-governorate/' + govId,
                        type: 'GET',
                        success: function (data) {
                            $('#directorate').html('<option value="">كل المديريات</option>');
                            $.each(data, function (key, value) {
                                $('#directorate').append(
                                    `<option value="${value.id}">${value.name}</option>`
                                );
                            });
                        },
                        error: function() {
                            $('#directorate').html('<option value="">كل المديريات</option>');
                        }
                    });
                } else {
                    $('#directorate').html('<option value="">كل المديريات</option>');
                }
            });
        });
    </script>
@endpush