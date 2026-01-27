<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'json',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }
}
