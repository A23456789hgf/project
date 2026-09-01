@php
    $isAllGov = false;
    $isAllDir = false;
    $govId = is_object($scope) ? ($scope->governorate_id ?? '') : ($scope['governorate_id'] ?? '');
    $dirId = is_object($scope) ? ($scope->directorate_id ?? '') : ($scope['directorate_id'] ?? '');

    if ($govId === 'all') {
        $isAllGov = true;
    }
    if ($dirId === 'all') {
        $isAllDir = true;
    }
@endphp
<tr class="geo-row">
    <td data-label="المحافظة">
        <select name="geographic_scopes[{{ $index }}][governorate_id]"
            class="form-select governorate-select">
            <option value="">-- بدون تحديد (اختياري) --</option>
            <option value="all" @selected($isAllGov)>كل المحافظات</option>
            @foreach($governorates as $gov)
                <option value="{{ $gov->id }}" @selected($govId == $gov->id)>
                    {{ $gov->name }}
                </option>
            @endforeach
        </select>
    </td>
    <td data-label="المديرية">
        <select name="geographic_scopes[{{ $index }}][directorate_id]"
            class="form-select directorate-select" 
            data-selected="{{ $dirId }}">
            <option value="">-- بدون تحديد (اختياري) --</option>
            @if(!$isAllDir && !empty($dirId) && $dirId !== 'all')
                @php $directorate = \App\Models\Directorate::find($dirId); @endphp
                @if($directorate)
                    <option value="{{ $directorate->id }}" selected>{{ $directorate->name }}</option>
                @endif
            @endif
        </select>
    </td>
    <td data-label="الإجراءات" class="text-center">
        <div class="project-action-buttons">
            <button type="button" class="project-btn project-btn-danger remove-geo-row" title="حذف">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

