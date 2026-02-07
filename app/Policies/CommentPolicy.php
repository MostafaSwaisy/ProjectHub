<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine if the user can update the comment.
     * Only allowed within 15 minutes of creation AND must be the author.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Must be the author
        if ($comment->user_id !== $user->id) {
            return false;
        }

        // Must be within 15 minutes of creation
        $editWindowMinutes = 15;
        $createdAt = $comment->created_at;
        $cutoffTime = $createdAt->addMinutes($editWindowMinutes);

        return now()->lt($cutoffTime);
    }

    /**
     * Determine if the user can delete the comment.
     * Only the author can delete their comments.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }
}
