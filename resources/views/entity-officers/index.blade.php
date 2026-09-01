@extends('layouts.app')

@section('title', 'مسؤولي الجهات')

@section('content')
@include('configuration.shared_styles')

    <x-index-page title="مسؤولي الجهات" icon="users">
        <x-slot name="headerActions">
            @can('entity_officers.create')
            <a href="{{ route('entity-officers.create') }}" class="btn btn-primary auth-perm-entity_officers-create">
                <x-icon name="plus" size="14" /> إضافة مسؤول
            </a>
            @endcan
        </x-slot>

        <x-slot name="table">
            <table class="table table-hover table-striped align-middle table-compact mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>اسم المسؤول</th>
                        <th>المسمى الوظيفي</th>
                        <th>نوع الجهة</th>
                        <th>الجهة</th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($officers as $officer)
                    <tr>
                        <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td class="text-name">{{ $officer->admin_name }}</td>
                        <td>{{ $officer->job_title }}</td>
                        <td>
                            @if($officer->entity_type === 'internal')
                                <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">داخلية</span>
                            @else
                                <span class="badge bg-warning-soft text-warning px-3 py-2 rounded-pill">خارجية</span>
                            @endif
                        </td>
                        <td>{{ $officer->entity_name }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                @can('entity_officers.edit')
                                <a href="{{ route('entity-officers.edit', $officer) }}" class="btn btn-action-edit btn-icon" title="تعديل">
                                    <x-icon name="edit-2" class="action-icon" />
                                </a>
                                @endcan
                                
                                @can('entity_officers.delete')
                                <form action="{{ route('entity-officers.destroy', $officer) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-action-delete btn-icon" onclick="return confirmAction(this, 'هل تريد الحذف؟')">
                                        <x-icon name="trash-2" class="action-icon" />
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">لا توجد بيانات متاحة حالياً</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </table>
        </x-slot>

        <x-slot name="pagination">
            @if(method_exists($officers, 'hasPages') && $officers->hasPages())
            <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
                <div class="text-muted small">
                    عرض <strong>{{ $officers->firstItem() }}</strong> إلى <strong>{{ $officers->lastItem() }}</strong> من <strong>{{ $officers->total() }}</strong> مسؤول
                </div>
                <div>{{ $officers->links() }}</div>
            </div>
            @endif
        </x-slot>
    </x-index-page>
@endsection
