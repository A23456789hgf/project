<?php

// app/Models/Subdomain.php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subdomain extends Model
{
    use Auditable, HasCreatorTracking, HasDomainScope, HasFactory;

    protected $table = 'subdomains'; // Explicitly set table name

    protected $fillable = [
        'status', 'name', 'domain_id', 'is_active', 'created_by_entity', 'created_by_user_id', 'creator_username', 'creator_entity_id'];

    //   public function domain(): BelongsTo
    //     {
    //         return $this->belongsTo(Domain::class);
    //     }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }
}
