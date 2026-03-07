<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrashItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = $this['type'];
        $modelInstance = $this['model_instance'];

        // Build the deleted_by user info
        $deletedByUser = null;
        if ($modelInstance->deletedBy) {
            $deletedByUser = [
                'id' => $modelInstance->deletedBy->id,
                'name' => $modelInstance->deletedBy->name,
                'email' => $modelInstance->deletedBy->email,
            ];
        }

        // Build parent info based on type
        $parent = null;
        if ($type === 'task') {
            $column = $modelInstance->column;
            if ($column) {
                $parent = [
                    'type' => 'column',
                    'id' => $column->id,
                    'title' => $column->title,
                    'exists' => true,
                ];
            }
        } elseif ($type === 'subtask') {
            $task = $modelInstance->task;
            if ($task) {
                $parent = [
                    'type' => 'task',
                    'id' => $task->id,
                    'title' => $task->title,
                    'exists' => $task->trashed() ? false : true,
                ];
            }
        } elseif ($type === 'comment') {
            $task = $modelInstance->task;
            if ($task) {
                $parent = [
                    'type' => 'task',
                    'id' => $task->id,
                    'title' => $task->title,
                    'exists' => $task->trashed() ? false : true,
                ];
            }
        } elseif ($type === 'column') {
            $board = $modelInstance->board;
            if ($board) {
                $parent = [
                    'type' => 'board',
                    'id' => $board->id,
                    'title' => $board->title,
                    'exists' => $board->trashed() ? false : true,
                ];
            }
        } elseif ($type === 'board') {
            $project = $modelInstance->project;
            if ($project) {
                $parent = [
                    'type' => 'project',
                    'id' => $project->id,
                    'title' => $project->title,
                    'exists' => true,
                ];
            }
        }

        return [
            'id' => $this['id'],
            'type' => $type,
            'title' => $this['title'],
            'deleted_at' => $this['deleted_at']?->toISOString(),
            'deleted_by' => $deletedByUser,
            'parent' => $parent,
        ];
    }
}
