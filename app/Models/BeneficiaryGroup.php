<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BeneficiaryGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'status', 'name'];

    protected $table = 'beneficiary_groups';

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'beneficiary_group_project');
    }
}
