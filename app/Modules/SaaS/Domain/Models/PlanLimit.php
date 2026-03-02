<?php

namespace App\Modules\SaaS\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'int',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}

