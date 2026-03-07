<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasSoftDeleteUser;
use App\Traits\HasCascadeSoftDeletes;
use App\Models\User;

class Column extends Model
{
    use HasFactory, SoftDeletes, HasSoftDeleteUser, HasCascadeSoftDeletes;

    protected $cascadeDeletes = ['tasks'];

    protected $fillable = [
        'board_id',
        'title',
        'position',
        'wip_limit',
        'deleted_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
