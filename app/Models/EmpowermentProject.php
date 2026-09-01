<?php

namespace App\Models;

use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmpowermentProject extends Model
{
    use HasCreatorTracking, HasDomainScope, HasFactory;

    /**
     * booted() — Register the global visibility scope so all queries on
     * EmpowermentProject are automatically filtered by the user's geo / admin scope.
     */
    protected $table = 'empowerment_projects';

    protected $fillable = [
        'geographic_scope_id',
        'administrative_scope_id',
        'project_id',
        'project_number',
        'project_name',
        'submitting_entity',
        'total_project_cost',
        'total_loan_amount',
        'loan_percentage',
        'number_of_beneficiaries',
        'start_date_gregorian',
        'start_date_hijri',
        'end_date_gregorian',
        'end_date_hijri',
        'status',
        'notes',
        'processed_by',
        'processed_at',
        'creator_username',
        'creator_entity_id',
    ];

    protected $casts = [
        'total_project_cost' => 'decimal:2',
        'total_loan_amount' => 'decimal:2',
        'loan_percentage' => 'decimal:4',
        'start_date_gregorian' => 'date',
        'end_date_gregorian' => 'date',
        'processed_at' => 'datetime',
    ];

    /** الحالات المتاحة */
    const STATUSES = [
        'pending' => ['label' => 'بانتظار المعالجة', 'badge' => 'warning', 'icon' => 'fa-clock'],
        'under_review' => ['label' => 'قيد المراجعة', 'badge' => 'info', 'icon' => 'fa-search'],
        'processed' => ['label' => 'تمت المعالجة', 'badge' => 'success', 'icon' => 'fa-check-circle'],
        'rejected' => ['label' => 'مرفوض', 'badge' => 'danger', 'icon' => 'fa-times-circle'],
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUSES[$this->status]['badge'] ?? 'secondary';
    }

    public function getStatusIconAttribute(): string
    {
        return self::STATUSES[$this->status]['icon'] ?? 'fa-circle';
    }

    /** العلاقة بالمشروع الأصلي */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** المستخدم الذي قام بالمعالجة */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /** المستفيدون المرتبطون بهذا المشروع */
    public function beneficiaries(): HasMany
    {
        return $this->hasMany(EmpowermentBeneficiary::class);
    }
}
