<div class="form-section">
    <h3>تكلفة المشروع</h3>

    <div class="row">
        <div class="form-group col-md-6">
            <label>إجمالي تكلفة المشروع</label>
            <input type="number" step="0.01" name="project_cost[total_cost]" 
                   value="{{ old('project_cost.total_cost', $project->cost->total_cost ?? '') }}" class="form-control">
        </div>
    </div>
</div>
