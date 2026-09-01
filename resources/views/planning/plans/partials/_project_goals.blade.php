@php
    $projIndex = $index ?? 'INDEX';
    $isReadOnly = $readOnlyProjects ?? false;
    $projectGoals = isset($project) && $project->goals ? $project->goals : collect();
@endphp

<div class="project-goals-section mt-3 p-3 bg-light rounded border" style="display: none;">
    <label class="fw-bold mb-2 text-dark d-flex align-items-center">
        <i class="fas fa-bullseye text-primary me-2"></i> الأهداف المحددة للمشروع
    </label>
    <table class="table table-bordered table-sm goals-table align-middle bg-white">
        <thead class="table-secondary">
            <tr style="font-size: 0.8rem;">
                <th>الهدف المحدد</th>
                <th style="width: 15%;">الوزن %</th>
                <th style="width: 15%;">قيمة المؤشر</th>
                <th style="width: 15%;">وحدة القياس</th>
                @if(!$isReadOnly)
                    <th style="width: 8%;" class="text-center">إجراء</th>
                @endif
            </tr>
        </thead>
        <tbody class="goals-tbody">
            @foreach($projectGoals as $gIndex => $goal)
                <tr>
                    <td>
                        <input type="text" name="projects[{{ $projIndex }}][goals][{{ $gIndex }}][specific_goal]"
                            class="form-control form-control-sm" value="{{ $goal->specific_goal }}" {{ $isReadOnly ? 'readonly' : 'required' }}>
                    </td>
                    <td>
                        <input type="number" name="projects[{{ $projIndex }}][goals][{{ $gIndex }}][weight]"
                            class="form-control form-control-sm text-center" value="{{ $goal->weight }}" step="0.01" {{ $isReadOnly ? 'readonly' : 'required' }}>
                    </td>
                    <td>
                        <input type="number" name="projects[{{ $projIndex }}][goals][{{ $gIndex }}][indicator_value]"
                            class="form-control form-control-sm text-center" value="{{ $goal->indicator_value }}" step="0.01" {{ $isReadOnly ? 'readonly' : 'required' }}>
                    </td>
                    <td>
                        <input type="text" name="projects[{{ $projIndex }}][goals][{{ $gIndex }}][unit_of_measurement]"
                            class="form-control form-control-sm" value="{{ $goal->unit_of_measurement }}" {{ $isReadOnly ? 'readonly' : 'required' }}>
                    </td>
                    @if(!$isReadOnly)
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-goal">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    @if(!$isReadOnly)
        <button type="button" class="btn btn-sm btn-outline-primary btn-add-goal" data-project-index="{{ $projIndex }}">
            <i class="fas fa-plus me-1"></i> إضافة هدف محدد
        </button>
    @endif
</div>
