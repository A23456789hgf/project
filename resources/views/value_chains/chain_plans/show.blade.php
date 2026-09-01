@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">عرض تفاصيل خطة السلسلة</h3>
            <div class="d-flex gap-2">
                @can('chain_plans.print', $chainPlan)
                    <a href="{{ route('chain_plans.print', $chainPlan->id) }}" target="_blank" class="btn btn-secondary">
                        <x-icon name="print" /> طباعة
                    </a>
                @endcan
                <a href="{{ route('chain_plans.index') }}" class="btn btn-outline-secondary">رجوع إلى القائمة</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>المحافظة:</strong>
                        <div>{{ $chainPlan->governorate->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>المديرية:</strong>
                        <div>{{ $chainPlan->directorate->name ?? '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>السلسلة:</strong>
                        <div>{{ $chainPlan->valueChain->name ?? $chainPlan->value_chain_id ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>المجال:</strong>
                        <div>{{ $chainPlan->domain->name ?? $chainPlan->domain_id ?? '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>اسم المشروع:</strong>
                        <div>{{ $chainPlan->project_name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>اسم النشاط:</strong>
                        <div>{{ $chainPlan->activity_name ?? '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>المؤشر:</strong>
                        <div>{{ $chainPlan->indicator ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>العدد:</strong>
                        <div>{{ $chainPlan->number ?? '-' }}</div>
                    </div>

                    @php
                        $formatAuthorities = function($value) {
                            if (empty($value) || $value === '-') return '-';
                            $ids = is_array($value) ? $value : (json_decode($value, true) ?: [$value]);
                            if (empty($ids)) return '-';
                            $names = \App\Models\Authority::whereIn('id', (array)$ids)->pluck('agency_name')->toArray();
                            return !empty($names) ? implode('، ', $names) : (is_array($value) ? implode('، ', $value) : $value);
                        };
                    @endphp
                    <div class="col-md-6">
                        <strong>نوع التمويل:</strong>
                        <div>{{ $chainPlan->financingType->name ?? $chainPlan->value_chain_financing_type_id ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>مصدر التمويل:</strong>
                        <div>{{ $formatAuthorities($chainPlan->funding_source_id) }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>الجهة المنفذة:</strong>
                        <div>{{ $formatAuthorities($chainPlan->authority_id) }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>الجهة المشرفة:</strong>
                        <div>{{ $formatAuthorities($chainPlan->implementing_entity_id) }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>تاريخ الإنشاء:</strong>
                        <div>{{ $chainPlan->created_at ? $chainPlan->created_at->format('Y-m-d H:i') : '-' }}</div>
                    </div>

                    <div class="col-md-6">
                        <strong>آخر تعديل:</strong>
                        <div>{{ $chainPlan->updated_at ? $chainPlan->updated_at->format('Y-m-d H:i') : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
