<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
}
