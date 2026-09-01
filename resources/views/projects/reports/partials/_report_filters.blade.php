{{-- فلتر موحد لتقارير المشاريع بحسب النطاق الجغرافي والإداري مع تعيين جهة المستخدم افتراضياً --}}
<div class="filter-card shadow-sm border-0 rounded-4 p-3 mb-4 no-print bg-white">
    <form id="report-filter-form" action="{{ $actionUrl ?? url()->current() }}" method="GET">
        <div class="row g-2 align-items-end">
            {{-- بحث بالاسم أو الكود --}}
            <div class="col-12 col-md-2">
                <label for="filter_search" class="form-label text-muted small fw-bold mb-1">بحث</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-0 bg-light" id="filter_search" name="search" value="{{ request('search') }}" placeholder="اسم أو رقم المشروع...">
                </div>
            </div>

            {{-- الجهة (الافتراضي هو جهة المستخدم) --}}
            <div class="col-12 col-md-3">
                <label for="filter_organization" class="form-label text-muted small fw-bold mb-1">
                    الجهة <span class="text-primary fw-normal">(الافتراضي: جهتك)</span>
                </label>
                <select class="form-select form-select-sm border-0 bg-light" id="filter_organization" name="organization" onchange="this.form.submit()">
                    <option value="all" {{ request('organization') === 'all' ? 'selected' : '' }}>-- كل الجهات المتاحة --</option>
                    @if(isset($entities) && $entities->isNotEmpty())
                        @php
                            $userEntityId = $defaultEntityId ?? auth()->user()->entity_id;
                            $selectedOrg = request('organization', request('entity', $userEntityId));
                        @endphp
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ (string)$selectedOrg === (string)$entity->id ? 'selected' : '' }}>
                                {{ $entity->name }} {{ (int)$entity->id === (int)$userEntityId ? '★ (جهتك)' : '' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- المحافظة --}}
            <div class="col-6 col-md-2">
                <label for="filter_governorate" class="form-label text-muted small fw-bold mb-1">المحافظة</label>
                <select class="form-select form-select-sm border-0 bg-light" id="filter_governorate" name="governorate" onchange="this.form.submit()">
                    <option value="">-- كل المحافظات --</option>
                    @if(isset($governorates))
                        @foreach($governorates as $gov)
                            <option value="{{ $gov->id }}" {{ request('governorate') == $gov->id ? 'selected' : '' }}>{{ $gov->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- العام الهجري --}}
            <div class="col-6 col-md-1">
                <label for="filter_hijri_year" class="form-label text-muted small fw-bold mb-1">العام</label>
                <select class="form-select form-select-sm border-0 bg-light" id="filter_hijri_year" name="hijri_year" onchange="this.form.submit()">
                    <option value="">الكل</option>
                    @if(isset($hijriYears))
                        @foreach($hijriYears as $year)
                            <option value="{{ $year }}" {{ request('hijri_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- المجال --}}
            <div class="col-6 col-md-2">
                <label for="filter_domain" class="form-label text-muted small fw-bold mb-1">المجال</label>
                <select class="form-select form-select-sm border-0 bg-light" id="filter_domain" name="domain" onchange="this.form.submit()">
                    <option value="">-- كل المجالات --</option>
                    @if(isset($domains))
                        @foreach($domains as $domain)
                            <option value="{{ $domain->id }}" {{ request('domain') == $domain->id ? 'selected' : '' }}>{{ $domain->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- فلاتر إضافية مخصصة لكل صفحة --}}
            @if(isset($slot) && !empty(trim($slot)))
                {{ $slot }}
            @endif

            {{-- أزرار التحكم --}}
            <div class="col-12 col-md-auto d-flex align-items-end gap-1 ms-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill bg-report-primary border-0" title="تطبيق الفلترة">
                    <i class="fas fa-filter me-1"></i> تصفية
                </button>
                <a href="{{ $actionUrl ?? url()->current() }}?organization=all" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" title="عرض جميع الجهات">
                    <i class="fas fa-redo me-1"></i> الكل
                </a>
            </div>
        </div>
    </form>
</div>
