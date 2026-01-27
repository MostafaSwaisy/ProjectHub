<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'column_id',
        'title',
        'description',
        'assignee_id',
        'priority',
        'due_date',
        'position',
    ];

    protected $casts = [
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'progress',
        'completed_subtask_count',
    ];

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)->orderBy('position');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'task_labels', 'task_id', 'label_id')
            ->withTimestamps();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'subject_id')
            ->where('subject_type', self::class);
    }

    /**
     * Get the progress percentage based on completed subtasks.
     * Returns the percentage of completed subtasks.
     *
     * @return int
     */
    public function getProgressAttribute(): int
    {
        $totalSubtasks = $this->subtasks()->count();

        if ($totalSubtasks === 0) {
            return 0;
        }

        $completedSubtasks = $this->subtasks()->where('is_completed', true)->count();

        return (int) (($completedSubtasks / $totalSubtasks) * 100);
    }

    /**
     * Get the count of completed subtasks.
     *
     * @return int
     */
    public function getCompletedSubtaskCountAttribute(): int
    {
        return $this->subtasks()->where('is_completed', true)->count();
    }

    /**
     * Determine if the task is overdue.
     * Returns true if due_date is in the past and task is not completed.
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date < now()->startOfDay();
    }
}
