@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('memoirs.index') }}">المذكرات</a></li>
                    <li class="breadcrumb-item active">تفاصيل المذكرة رقم {{ $memoir->memoir_number }}</li>
                </ol>
            </nav>

            <div class="actions">
                @can('memoirs.edit', $memoir)
                    <a href="{{ route('memoirs.edit', $memoir->id) }}" class="btn btn-warning shadow-sm">
                        <i class="fas fa-edit me-1"></i> تعديل البيانات
                    </a>
                @endcan
                <div class="no-print d-inline-block">
                    <button type="button" onclick="openPrintSettings({{ $memoir->id }})" class="btn btn-primary shadow-sm">
                        <i class="fas fa-print me-1"></i> طباعة
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-file-alt me-2"></i> تفاصيل المذكرة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="text-muted small d-block">الموضوع</label>
                            <h3 class="fw-bold">{{ $memoir->subject }}</h3>
                        </div>
                        <hr>
                        <div class="content-view py-3" style="font-size: 1.1rem; line-height: 1.8; white-space: pre-wrap;">
                            {{ $memoir->body }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light py-3">
                        <h6 class="mb-0 fw-bold">معلومات إضافية</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">رقم المذكرة</span>
                                <span class="fw-bold text-dark">{{ $memoir->memoir_number }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">جهة الصدور</span>
                                <span class="fw-bold">{{ $memoir->entity?->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">موجهة إلى</span>
                                <span class="fw-bold text-primary">{{ $memoir->to }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">التاريخ الهجري</span>
                                <span>{{ $memoir->hijri_date }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">التاريخ الميلادي</span>
                                <span>{{ $memoir->gregorian_date }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar-circle bg-soft-primary text-primary me-3">
                            <i class="fas fa-user-edit fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">بواسطة</small>
                            <span class="fw-bold">{{ $memoir->creator?->name }}</span>
                            <small class="d-block text-muted">{{ $memoir->creator?->work }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* تحسينات الواجهة للعرض فقط */
        .card {
            border-radius: 12px;
        }

        .bg-soft-primary {
            background-color: #e7f1ff;
            padding: 15px;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 12px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "\f104";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            padding: 0 10px;
        }

        /* منع الطباعة من هذه الصفحة للحفاظ على شكل "النظام" */
        @media print {
            .container {
                display: none;
            }
        }
    </style>
@include('memoirs.partials.print_settings_modal')
@endsection