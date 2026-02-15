<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'is_archived',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    /**
     * Map database enum values to API contract format
     * Database: 'on_track', 'behind', 'ahead'
     * API: 'On Track', 'At Risk', 'Delayed'
     */
    protected function timelineStatus(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => match($value) {
                'on_track' => 'On Track',
                'behind' => 'At Risk',
                'ahead' => 'Ahead',
                default => 'On Track'
            },
            set: fn ($value) => match($value) {
                'On Track' => 'on_track',
                'At Risk' => 'behind',
                'Ahead' => 'ahead',
                default => 'on_track'
            }
        );
    }

    /**
     * Map database enum values to API contract format
     * Database: 'over_budget', 'on_budget', 'under_budget'
     * API: 'Within Budget', 'Over Budget'
     */
    protected function budgetStatus(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => match($value) {
                'on_budget' => 'Within Budget',
                'under_budget' => 'Within Budget',
                'over_budget' => 'Over Budget',
                default => 'Within Budget'
            },
            set: fn ($value) => match($value) {
                'Within Budget' => 'on_budget',
                'Over Budget' => 'over_budget',
                default => 'on_budget'
            }
        );
    }

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

    /**
     * Scope to filter only active (non-archived) projects
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope to filter only archived projects
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope to get projects for a specific user (as instructor or member)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('instructor_id', $userId)
                ->orWhereHas('members', function ($memberQuery) use ($userId) {
                    $memberQuery->where('user_id', $userId);
                });
        });
    }
}
