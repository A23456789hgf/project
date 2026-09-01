<tr class="execution-row" id="exec-row-existing-{{ $exec->id }}" data-exec-id="{{ $exec->id }}">
    <td class="text-center fw-bold">{{ $index }}</td>
    <td>
        <input type="date" class="form-control form-control-sm actual-start-gregorian" 
               value="{{ $exec->actual_start_date_gregorian ?? '' }}"
               data-hijri-field="hijri_start_exec_{{ $exec->id }}">
    </td>
    <td>
        <input type="text" class="form-control form-control-sm bg-light hijri-start" 
               id="hijri_start_exec_{{ $exec->id }}" 
               value="{{ $exec->actual_start_date_hijri ?? '' }}"
               readonly>
    </td>
    <td>
        <input type="date" class="form-control form-control-sm actual-finish-gregorian" 
               value="{{ $exec->actual_finish_date_gregorian ?? '' }}"
               data-hijri-field="hijri_finish_exec_{{ $exec->id }}">
    </td>
    <td>
        <input type="text" class="form-control form-control-sm bg-light hijri-finish" 
               id="hijri_finish_exec_{{ $exec->id }}" 
               value="{{ $exec->actual_finish_date_hijri ?? '' }}"
               readonly>
    </td>
    <td class="text-end">
        <input type="number" class="form-control form-control-sm actual-amount" 
               step="0.01" min="0"
               value="{{ $exec->actual_amount ?? 0 }}">
    </td>
    <td class="text-end">
        <input type="number" class="form-control form-control-sm amount-spent" 
               step="0.01" min="0"
               value="{{ $exec->amount_spent ?? 0 }}">
    </td>
    <td class="text-end">
        <input type="text" class="form-control form-control-sm bg-light remaining-amount" 
               value="{{ number_format($exec->remaining_amount ?? 0, 2) }}"
               readonly>
    </td>
    <td class="text-center">
        <input type="number" class="form-control form-control-sm completion-pct text-center" 
               step="0.01" min="0" max="100"
               value="{{ $exec->completion_percentage ?? 0 }}">
    </td>
    <td class="text-center">
        <select class="form-select form-select-sm exec-status">
            <option value="not_started" {{ ($exec->status ?? 'not_started') === 'not_started' ? 'selected' : '' }}>لم يبدأ</option>
            <option value="in_progress" {{ $exec->status === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
            <option value="delayed" {{ $exec->status === 'delayed' ? 'selected' : '' }}>متأخر</option>
            <option value="stalled" {{ $exec->status === 'stalled' ? 'selected' : '' }}>متعثر</option>
            <option value="completed" {{ $exec->status === 'completed' ? 'selected' : '' }}>مكتمل</option>
        </select>
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-secondary view-attachments" 
                data-exec-id="{{ $exec->id }}" 
                data-procedure-id="{{ $procedureId }}"
                title="عرض المرفقات">
            <i class="fas fa-paperclip"></i>
        </button>
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-danger delete-execution-row" 
                data-exec-id="{{ $exec->id }}"
                title="حذف">
            <i class="fas fa-trash-alt"></i>
        </button>
    </td>
</tr>
