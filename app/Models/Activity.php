<?php

namespace App\Models;

use App\Traits\HasSoftDeleteUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes, HasSoftDeleteUser;

    protected $fillable = [
        'user_id',
        'project_id',
        'type',
        'subject_type',
        'subject_id',
        'data',
        'deleted_by',
    ];

    protected $casts = [
        'data' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function subject()
    {
        return $this->morphTo(__FUNCTION__, 'subject_type', 'subject_id');
    }
}
