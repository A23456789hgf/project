<div class="action-buttons" style="margin-top: 2rem;">
    @if(in_array($project->status, ['draft', 'completed_draft']) && $project->isDraftComplete())
    @can('projects.finalize', $project)
    <form action="{{ route('projects.finalize', $project->id) }}" method="POST" style="display:inline;" class="auth-perm-projects-finalize" data-perm="projects.finalize">
        @csrf
        <button type="submit" class="btn-action btn-approve" onclick="return confirmAction(this, 'هل تريد الموافقة على هذا المشروع؟')">
            <i class="fas fa-check-circle"></i> الموافقة
        </button>
    </form>
    @endcan
    @endif

    @if($project->status === 'final')
    @can('projects.view', $project)
    <a href="{{ route('projects.show', $project->id) }}" class="btn-action btn-view auth-perm-projects-view" data-perm="projects.view">
        <i class="fas fa-file-alt"></i> View Project
    </a>
    @endcan
    @can('projects.view', $project)
    <a href="{{ route('projects.show', $project->id) }}?view=card" class="btn-action btn-card auth-perm-projects-view-details" data-perm="projects.view-details">
        <i class="fas fa-th-large"></i> View Project Card
    </a>
    @endcan
    @can('projects.print', $project)
    <button class="btn-action btn-print auth-perm-projects-print" data-perm="projects.print" onclick="window.open('{{ route('projects.print', $project->id) }}', '_blank')">
        <i class="fas fa-print"></i> Print Project
    </button>
    @endcan
    @endif

    @if(in_array($project->status, ['draft', 'completed_draft']))
    @can('projects.edit', $project)
    <a href="{{ route('projects.edit', $project->id) }}" class="btn-action btn-edit auth-perm-projects-edit" data-perm="projects.edit">
        <i class="fas fa-edit"></i> تعديل
    </a>
    @endcan
    @endif

    <a href="{{ route('projects.index') }}" class="btn-action btn-back">
        <i class="fas fa-arrow-right"></i> عودة
    </a>
</div>
