<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasSoftDeleteUser
{
    /**
     * Boot the trait.
     *
     * Set up model event listeners for tracking who deleted the model.
     */
    protected static function bootHasSoftDeleteUser(): void
    {
        /**
         * Before deleting, capture the authenticated user's ID.
         */
        static::deleting(function (Model $model) {
            // Only set deleted_by if the model is being soft-deleted (not force-deleted)
            if (!$model->isForceDeleting()) {
                $model->deleted_by = auth()->id();
            }
        });

        /**
         * When restoring, clear the deleted_by field.
         */
        static::restoring(function (Model $model) {
            $model->deleted_by = null;
        });
    }
}
