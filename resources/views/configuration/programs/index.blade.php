@extends('layouts.app')

@section('content')
    @include('configuration.shared_styles')
    <link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

    @cannot('viewAny', App\Models\Program::class)
    @php abort(403); @endphp
    @endcannot



    <div class="container">
        <div class="main-card p-4 bg-white rounded-4 shadow-sm border-0">

            <div class="page-header d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h2 class="fw-bold mb-0 d-flex align-items-center gap-2" style="color: #0f172a;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512" fill="#3b82f6">
                            <path
                                d="M416 160V112c0-26.5-21.5-48-48-48H144c-26.5 0-48 21.5-48 48v48H0v80h96v112H48c-26.5 0-48 21.5-48 48v48h144v-48c0-26.5-21.5-48-48-48H0V240h96v-48h224v48h96v112H368c-26.5 0-48 21.5-48 48v48h144v-48c0-26.5-21.5-48-48-48H368V240h96v-80H416z" />
                        </svg>
                        قائمة البرامج
                    </h2>
                    <div class="title-line mt-2"
                        style="width: 50px; height: 3px; background-color: #3b82f6; border-radius: 2px;"></div>
                </div>

                <div class="d-flex gap-2">
                    {{-- Create --}}
                    @can('create', App\Models\Program::class)
                        <a href="{{ route('programs.create') }}"
                            class="btn btn-success d-flex align-items-center gap-2 px-4 rounded-3 fw-bold shadow-sm auth-perm-programs-create">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 448 512"
                                fill="currentColor">
                                <path
                                    d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z" />
                            </svg>
                            إضافة برنامج
                        </a>
                    @endcan

                    {{-- Data Dropdown --}}
                    @canany(['export', 'import'], App\Models\Program::class)
                        <div class="dropdown">
                            <button class="btn btn-dark d-flex align-items-center gap-2 dropdown-toggle shadow-sm rounded-3"
                                type="button" data-bs-toggle="dropdown">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 448 512"
                                    fill="currentColor">
                                    <path
                                        d="M448 80v48c0 44.2-100.3 80-224 80S0 172.2 0 128V80C0 35.8 100.3 0 224 0S448 35.8 448 80zM393.2 214.7c20.8-7.4 39.9-16.9 54.8-28.6V288c0 44.2-100.3 80-224 80S0 332.2 0 288V186.1c14.9 11.8 34 21.2 54.8 28.6C99.7 230.7 159.5 240 224 240s124.3-9.3 169.2-25.3zM0 346.1c14.9 11.8 34 21.2 54.8 28.6C99.7 390.7 159.5 400 224 400s124.3-9.3 169.2-25.3c20.8-7.4 39.9-16.9 54.8-28.6V432c0 44.2-100.3 80-224 80S0 476.2 0 432V346.1z" />
                                </svg>
                                البيانات
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2">
                                {{-- Export --}}
                                @can('export', App\Models\Program::class)
                                    <li>
                                        <a class="dropdown-item py-2 d-flex align-items-center gap-2 auth-perm-programs-export"
                                            href="{{ route('config.export', ['entity' => 'programs']) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 512 512"
                                                fill="#10b981">
                                                <path
                                                    d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V274.7l-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7V32zM64 352c-35.3 0-64 28.7-64 64v32c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V416c0-35.3-28.7-64-64-64H346.5l-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352H64zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                                            </svg>
                                            تصدير Excel
                                        </a>
                                    </li>
                                @endcan

                                {{-- Import --}}
                                @can('import', App\Models\Program::class)
                                    @can('export', App\Models\Program::class)
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                    @endcan
                                    <li>
                                        <a class="dropdown-item py-2 d-flex align-items-center gap-2 auth-perm-programs-import"
                                            href="{{ route('config.import', ['entity' => 'programs']) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 512 512"
                                                fill="#0ea5e9">
                                                <path
                                                    d="M288 109.3V352c0 17.7-14.3 32-32 32s-32-14.3-32-32V109.3l-73.4 73.4c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l128-128c12.5-12.5 32.8-12.5 45.3 0l128 128c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L288 109.3zM64 352H165.5l-45.3 45.3c-25 25-65.5 25-90.5 0L346.5 416H448c35.3 0 64 28.7 64 64v32c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V416c0-35.3 28.7-64 64-64zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                                            </svg>
                                            استيراد ملف
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    @endcanany
                </div>
            </div>

            {{-- Success Message --}}
            

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table custom-table w-100">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">#</th>
                            <th class="text-start">اسم البرنامج</th>
                            <th class="text-start">أنشئ بواسطة</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            @canany(['update', 'delete'], App\Models\Program::class)
                                <th class="text-center" style="width: 140px;">الإجراءات</th>
                            @endcanany
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($programs as $program)
                            <tr>
                                <td class="text-muted fw-bold text-center">
                                    {{ ($programs->currentPage() - 1) * $programs->perPage() + $loop->iteration }}
                                </td>

                                <td class="text-dark fw-bold">
                                    {{ $program->name }}
                                </td>

                                <td>
                                    @if($program->creator_username)
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">{{ $program->creator_username }}</span>
                                            <small class="text-muted" style="font-size: 0.8rem;">
                                                {{ $program->creatorEntity->name ?? '-' }}
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($program->is_active)
                                        <span class="status-badge status-active gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 512 512"
                                                fill="currentColor">
                                                <path
                                                    d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z" />
                                            </svg>
                                            نشط
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 512 512"
                                                fill="currentColor">
                                                <path
                                                    d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" />
                                            </svg>
                                            معطل
                                        </span>
                                    @endif
                                </td>

                                <td class="text-muted fw-medium">
                                    {{ $program->created_at->format('Y-m-d') }}
                                </td>

                                @canany(['update', 'delete'], $program)
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            {{-- Edit --}}
                                            @can('update', $program)
                                                <a href="{{ route('programs.edit', $program->id) }}"
                                                    class="btn-action btn-edit auth-perm-programs-edit" title="تعديل">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 512 512"
                                                        fill="currentColor">
                                                        <path
                                                            d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-29.9 127c-3.1-3.1-8.2-3.1-11.3 0L71.4 507.5c-4.6 4.6-11.5 5.9-17.4 3.5s-9.9-8.3-9.9-14.8V339.5c0-6.4 2.5-12.5 7-17l358.8-358.8c3.1-3.1 8.2-3.1 11.3 0l69.1 69.1c3.1 3.1 3.1 8.2 0 11.3l-48.8 48.8z" />
                                                    </svg>
                                                </a>
                                            @endcan

                                            {{-- Delete --}}
                                            @can('delete', $program)
                                                <form action="{{ route('programs.destroy', $program->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action btn-delete auth-perm-programs-delete"
                                                        title="حذف" onclick="return confirmAction(this, 'هل أنت متأكد أنك تريد الحذف؟')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            viewBox="0 0 448 512" fill="currentColor">
                                                            <path
                                                                d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 512 512"
                                        fill="#cbd5e1" class="mb-3 d-block mx-auto">
                                        <path
                                            d="M40 48C26.7 48 16 58.7 16 72v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V72c0-13.3-10.7-24-24-24H40zM192 64c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zM16 232v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V232c0-13.3-10.7-24-24-24H40c-13.3 0-24 10.7-24 24zM40 368c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V392c0-13.3-10.7-24-24-24H40z" />
                                    </svg>
                                    لا توجد بيانات متاحة حالياً
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($programs->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $programs->links() }}
                </div>
            @endif

        </div>
    </div>
@endsection