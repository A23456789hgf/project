<div class="card mt-4">
    <div class="card-header">الهدف العام</div>
    <div class="card-body">
        <div class="row">
           <div class="col-md-12">
    <div class="form-group">
        <label>الهدف العام</label>
        <textarea name="main_objective" class="form-control" rows="4">{{ old('main_objective', isset($project) ? $project->mainObjective?->objective ?? '' : '') }}</textarea>
    </div>
</div>

        </div>
    </div>
</div>
     @include('projects.partials.tables.specific_objectives')
 