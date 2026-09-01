<!-- Project Details Section -->
<div class="card mt-4">
    <div class="card-header">تفاصيل المشروع</div>
    <div class="card-body">

        <!-- Row 1 -->
        <div class="row">
            <div class="form-group col-md-6">
                <label for="project_introduction">مقدمة المشروع</label>
                <textarea class="form-control" id="project_introduction" name="project_introduction" rows="3">{{ old('project_introduction', optional($project->detail ?? null)->project_introduction ?? '') }}</textarea>
            </div>

            <div class="form-group col-md-6">
                <label for="project_summary">ملخص المشروع</label>
                <textarea class="form-control" id="project_summary" name="project_summary" rows="3">{{ old('project_summary', optional($project->detail ?? null)->project_summary ?? '') }}</textarea>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="row">
            <div class="form-group col-md-6">
                <label for="problem_and_justification">المشكلة ومبررات التدخل</label>
                <textarea class="form-control" id="problem_and_justification" name="problem_and_justification" rows="3">{{ old('problem_and_justification', optional($project->detail ?? null)->problem_and_justification ?? '') }}</textarea>
            </div>

            <div class="form-group col-md-6">
                <label for="project_components">مكونات المشروع</label>
                <textarea class="form-control" id="project_components" name="project_components" rows="3">{{ old('project_components', optional($project->detail ?? null)->project_components ?? '') }}</textarea>
            </div>
        </div>

        <!-- Row 3 -->
        <div class="row">
            <div class="form-group col-md-6">
                <label for="expected_impact">الاثر المتوقع (الاقتصادي - البيئي - الاجتماعي )</label>
                <textarea class="form-control" id="expected_impact" name="expected_impact" rows="3">{{ old('expected_impact', optional($project->detail ?? null)->expected_impact ?? '') }}</textarea>
            </div>

            <div class="form-group col-md-6">
                <label>هل المشروع جزء من الخطة؟</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_part_of_plan" id="plan_yes" value="1" 
                            {{ old('is_part_of_plan', optional($project->detail ?? null)->is_part_of_plan ?? null) ? 'checked' : '' }}>
                        <label class="form-check-label" for="plan_yes">نعم</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_part_of_plan" id="plan_no" value="0" 
                            {{ !old('is_part_of_plan', optional($project->detail ?? null)->is_part_of_plan ?? null) && !is_null(old('is_part_of_plan', optional($project->detail ?? null)->is_part_of_plan ?? null)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="plan_no">لا</label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
