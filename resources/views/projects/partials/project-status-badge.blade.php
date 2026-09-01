@if($project->status === 'final')
    <span class="badge bg-success px-3 py-2 rounded-pill">
        <i class="fas fa-check-circle me-1"></i> نهائي
    </span>
@elseif($project->status === 'completed_draft' || ($project->status === 'draft' && $project->isDraftComplete()))
    <span class="badge bg-success px-3 py-2 rounded-pill">
        <i class="fas fa-check-double me-1"></i> مسودة مكتملة
    </span>
@else
    <span class="badge bg-secondary px-3 py-2 rounded-pill">
        <i class="fas fa-file-alt me-1"></i> مسودة ({{ $project->getCompletionPercentage() }}%)
    </span>
@endif
