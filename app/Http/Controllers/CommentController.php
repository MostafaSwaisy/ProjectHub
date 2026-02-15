<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a task.
     */
    public function index(Task $task): AnonymousResourceCollection
    {
        $comments = $task->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->get();

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created comment.
     */
    public function store(StoreCommentRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();
        $validated['task_id'] = $task->id;
        $validated['user_id'] = auth()->id();

        $comment = Comment::create($validated);
        $comment->load('user');

        // Log activity
        $this->logActivity($task, 'comment.created', [
            'comment_id' => $comment->id,
            'excerpt' => \Str::limit($comment->body, 50),
        ]);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified comment.
     * Only allowed within 15 minutes of creation.
     */
    public function update(UpdateCommentRequest $request, Comment $comment): CommentResource|JsonResponse
    {
        // Check policy for 15-minute window
        if (Gate::denies('update', $comment)) {
            return response()->json([
                'message' => 'Comments can only be edited within 15 minutes of creation.',
            ], 403);
        }

        $validated = $request->validated();
        $comment->update([
            'body' => $validated['body'],
            'edited_at' => now(),
        ]);

        $comment->load('user');

        return new CommentResource($comment);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        // Check policy
        if (Gate::denies('delete', $comment)) {
            return response()->json([
                'message' => 'You can only delete your own comments.',
            ], 403);
        }

        $comment->delete();

        return response()->json(null, 204);
    }

    /**
     * Log an activity for the task.
     */
    private function logActivity(Task $task, string $type, array $data = []): void
    {
        $column = $task->column;
        $board = $column?->board;
        $projectId = $board?->project_id;

        if ($projectId) {
            Activity::create([
                'user_id' => auth()->id(),
                'project_id' => $projectId,
                'type' => $type,
                'subject_type' => Comment::class,
                'subject_id' => $data['comment_id'] ?? null,
                'data' => $data,
            ]);
        }
    }
}
