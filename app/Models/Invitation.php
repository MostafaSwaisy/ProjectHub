<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'invited_by',
        'email',
        'role',
        'token',
        'status',
        'email_sent',
        'accepted_at',
        'expires_at',
    ];

    protected $casts = [
        'email_sent' => 'boolean',
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the project this invitation is for
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who sent this invitation
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Scope to get pending invitations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get expired invitations
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->whereDate('expires_at', '<', now());
    }

    /**
     * Scope to get non-expired pending invitations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->whereDate('expires_at', '>=', now());
    }

    /**
     * Generate a unique token for this invitation
     */
    public static function generateToken(): string
    {
        return Str::random(40);
    }

    /**
     * Check if this invitation is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Accept this invitation
     */
    public function accept(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Decline this invitation
     */
    public function decline(): void
    {
        $this->update(['status' => 'declined']);
    }

    /**
     * Mark as expired
     */
    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }
}
