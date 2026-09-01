<?php

namespace App\Models;

use App\Services\ProjectNumberGenerator;
use App\Traits\Auditable;
use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRequest extends Model
{
    use Auditable, HasCreatorTracking, HasDomainScope, HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'created_by_user_id',
        'updated_by_user_id',
        'project_name',
        'project_data',
        'program_id',
        'domain_id',
        'subdomain_id',
        'intervention_id',
        'main_router_id',
        'sub_router_id',
        'priority_id',
        'target_category_id',
        'start_date_gregorian',
        'start_date_hijri',
        'end_date_gregorian',
        'end_date_hijri',
        'number_of_beneficiaries',
        'status',
        'last_saved_step',
        'current_stage',
        'current_stage_order',
        'approval_status',
        'current_approval_stage_id',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'transferred_at',
        'project_id',
        'assigned_project_number',
        'approved_by_user_id',
        'approval_notes',
        'creator_username',
        'creator_entity_id',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'transferred_at' => 'datetime',
        'project_data' => 'json',
    ];

    protected $appends = [
        'status_label',
        'status_color',
    ];

    // --- Accessors ---

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'مسودة',
            'submitted' => 'مرسلة',
            'pending_approval' => 'قيد الموافقة',
            'approved' => 'موافق عليها',
            'rejected' => 'مرفوضة',
            'transferred' => 'محولة للمشاريع',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'submitted' => 'info',
            'pending_approval' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'transferred' => 'primary',
            default => 'secondary',
        };
    }

    // --- Relationships ---

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function subdomain(): BelongsTo
    {
        return $this->belongsTo(Subdomain::class);
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function detail()
    {
        return $this->hasOne(ProjectDetail::class);
    }

    public function cost()
    {
        return $this->hasOne(ProjectCost::class);
    }

    public function mainObjectives()
    {
        return $this->hasMany(MainObjective::class);
    }

    public function specialObjectives()
    {
        return $this->hasMany(SpecialObjective::class);
    }

    public function locations()
    {
        return $this->hasMany(ProjectLocation::class);
    }

    public function risks()
    {
        return $this->hasMany(ProjectRisk::class);
    }

    public function supervisingAuthorities()
    {
        return $this->hasMany(ProjectSupervisingAuthority::class);
    }

    public function implementingEntities()
    {
        return $this->hasMany(ProjectImplementingEntity::class);
    }

    public function participatingEntities()
    {
        return $this->hasMany(ParticipatingEntity::class);
    }

    public function beneficiaryEntities()
    {
        return $this->hasMany(BeneficiaryEntity::class);
    }

    public function getProjectLinkedEntities()
    {
        $entities = collect();

        // 1. Implementing entities
        foreach ($this->implementingEntities()->with(['internalEntity', 'authority'])->get() as $entity) {
            $name = $entity->authority_type === 'internal'
                ? $entity->internalEntity?->name
                : $entity->authority?->agency_name;
            if ($name) {
                $entities->push($name);
            }
        }

        // 2. Participating entities
        foreach ($this->participatingEntities()->with(['internalEntity', 'authority'])->get() as $entity) {
            $name = $entity->authority_type === 'internal'
                ? $entity->internalEntity?->name
                : $entity->authority?->agency_name;
            if ($name) {
                $entities->push($name);
            }
        }

        // 3. Supervising authorities
        foreach ($this->supervisingAuthorities()->with(['internalEntity', 'authority'])->get() as $entity) {
            $name = $entity->authority_type === 'internal'
                ? $entity->internalEntity?->name
                : $entity->authority?->agency_name;
            if ($name) {
                $entities->push($name);
            }
        }

        // 4. Beneficiary entities
        foreach ($this->beneficiaryEntities()->with(['internalEntity', 'authority'])->get() as $entity) {
            $name = $entity->authority_type === 'internal'
                ? $entity->internalEntity?->name
                : $entity->authority?->agency_name;
            if ($name) {
                $entities->push($name);
            }
        }

        return $entities->unique()->values();
    }

    public function preliminaryActivities()
    {
        return $this->hasMany(PreliminaryActivity::class);
    }

    public function executiveActivities()
    {
        return $this->hasMany(ExecutiveActivity::class);
    }

    public function financings()
    {
        return $this->hasMany(ProjectFinancing::class);
    }

    public function preliminaryFinancialSummaries()
    {
        return $this->hasMany(PreliminaryFinancialSummary::class);
    }

    public function executiveFinancialSummaries()
    {
        return $this->hasMany(ExecutiveFinancialSummary::class);
    }

    public function mainRouter(): BelongsTo
    {
        return $this->belongsTo(MainRouter::class, 'main_router_id');
    }

    public function subRouter(): BelongsTo
    {
        return $this->belongsTo(SubRouter::class, 'sub_router_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    public function targetCategory(): BelongsTo
    {
        return $this->belongsTo(TargetCategory::class, 'target_category_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Get the name of the entity that created the project request.
     */
    public function getCreatorEntityNameAttribute(): string
    {
        if ($this->createdBy) {
            return $this->createdBy->department ?? '-';
        }

        return '-';
    }

    // --- Scopes ---

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['draft', 'submitted', 'pending_approval']);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeTransferred(Builder $query): Builder
    {
        return $query->where('status', 'transferred');
    }

    public function scopeByUser(Builder $query, $userId): Builder
    {
        return $query->where('created_by_user_id', $userId);
    }

    // --- Methods ---

    /**
     * Generate next request number
     * New Format: REQYYYY####
     */
    public static function generateRequestNumber(): string
    {
        return ProjectNumberGenerator::getNextRequestNumber();
    }

    /**
     * Mark request as submitted
     */
    public function markAsSubmitted(): bool
    {
        return $this->update([
            'status' => 'pending_approval',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Approve the request
     */
    public function approve(int $userId, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by_user_id' => $userId,
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Reject the request
     */
    public function reject(int $userId, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'approved_by_user_id' => $userId,
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Check if request can be approved
     */
    public function canBeApproved(): bool
    {
        return in_array($this->status, ['submitted', 'pending_approval', 'rejected']);
    }

    /**
     * Check if request can be transferred to project
     */
    public function canBeTransferred(): bool
    {
        return $this->status === 'approved' && ! $this->project_id;
    }

    /**
     * Check if request is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is transferred
     */
    public function isTransferred(): bool
    {
        return $this->status === 'transferred';
    }

    /**
     * Booted method (Model Events)
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (! $model->request_number) {
                $model->request_number = ProjectNumberGenerator::getNextRequestNumber();
            }
        });
    }
}
