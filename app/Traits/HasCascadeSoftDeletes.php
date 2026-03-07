<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasCascadeSoftDeletes
{
    /**
     * Boot the trait.
     *
     * Set up model event listeners for cascading soft deletes and restores.
     */
    protected static function bootHasCascadeSoftDeletes(): void
    {
        /**
         * When deleting, cascade the delete to related models.
         */
        static::deleting(function (Model $model) {
            // Check if the model has defined cascade relationships
            if (!isset($model->cascadeDeletes) || !is_array($model->cascadeDeletes)) {
                return;
            }

            // Only cascade soft deletes, not force deletes (those will be handled separately)
            if ($model->isForceDeleting()) {
                return;
            }

            foreach ($model->cascadeDeletes as $relationName) {
                if (!$model->relationLoaded($relationName)) {
                    $model->load($relationName);
                }

                $relation = $model->getRelation($relationName);

                if ($relation) {
                    if (is_iterable($relation)) {
                        foreach ($relation as $child) {
                            $child->delete();
                        }
                    } else {
                        $relation->delete();
                    }
                }
            }
        });

        /**
         * When restoring, restore only the children that were deleted at the same time.
         * This ensures we only restore children that were deleted together with the parent.
         */
        static::restoring(function (Model $model) {
            // Check if the model has defined cascade relationships
            if (!isset($model->cascadeDeletes) || !is_array($model->cascadeDeletes)) {
                return;
            }

            foreach ($model->cascadeDeletes as $relationName) {
                // Get the relationship query
                $relation = $model->{$relationName}();

                // Include soft-deleted records only
                $relation->onlyTrashed();

                // Get all soft-deleted children
                $children = $relation->get();

                foreach ($children as $child) {
                    // Only restore if deleted_at matches (deleted at same time)
                    if ($child->deleted_at && $model->deleted_at && $child->deleted_at->eq($model->deleted_at)) {
                        $child->restore();
                    }
                }
            }
        });

        /**
         * When force-deleting, cascade the force delete to related models.
         */
        static::forceDeleting(function (Model $model) {
            // Check if the model has defined cascade relationships
            if (!isset($model->cascadeDeletes) || !is_array($model->cascadeDeletes)) {
                return;
            }

            foreach ($model->cascadeDeletes as $relationName) {
                // Get the relationship query
                $relation = $model->{$relationName}();

                // Include soft-deleted records for force delete
                $relation->withTrashed();

                // Get all children (soft-deleted and active)
                $children = $relation->get();

                foreach ($children as $child) {
                    $child->forceDelete();
                }
            }
        });
    }
}
