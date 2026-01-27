<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'instructor_id',
        'timeline_status',
        'budget_status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function projectMembers(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function tasks()
    {
        return $this->hasManyThrough(
            Task::class,
            Column::class,
            'board_id',
            'column_id',
            'id',
            'id'
        )->join('boards', 'columns.board_id', '=', 'boards.id')
            ->where('boards.project_id', $this->id);
    }
}
