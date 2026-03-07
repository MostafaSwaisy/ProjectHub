<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Board extends Model
{
    use HasFactory, SoftDeletes;

    protected $cascadeDeletes = ['columns'];

    protected $fillable = [
        'project_id',
        'title',
        'deleted_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($board) {
            $defaultColumns = [
                ['title' => 'Backlog', 'position' => 1],
                ['title' => 'To Do', 'position' => 2],
                ['title' => 'In Progress', 'position' => 3],
                ['title' => 'Review', 'position' => 4],
                ['title' => 'Completed', 'position' => 5],
            ];

            foreach ($defaultColumns as $columnData) {
                Column::create([
                    'board_id' => $board->id,
                    'title' => $columnData['title'],
                    'position' => $columnData['position'],
                    'wip_limit' => 0,
                ]);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class)->orderBy('position');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
