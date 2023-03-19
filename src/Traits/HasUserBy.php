<?php

namespace LarawireGarage\SimpleMultitenancy\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Handle multitenancy with eloquent events
 * @property bool $userBy Indicates if the model should be mutitenanced.
 */
trait HasUserBy
{
    // **** relationships with User model
    public function createdBy()
    {
        return $this->belongsTo(User::class, $this->getCreatedByColumn(), 'id');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, $this->getUpdatedByColumn(), 'id');
    }
    public function deletedBy()
    {
        return $this->belongsTo(User::class, $this->getDeletedByColumn(), 'id');
    }


    /**
     * creating ,updating ,deleting ,saving events updates Userby columns
     */
    public static function bootHasUserBy()
    {
        static::creating(fn ($model) => $model->updateUserBy($model));
        static::updating(fn ($model) => $model->updateUserBy($model));

        static::deleting(function ($model) {
            if (method_exists(static::class, 'restore')) {
                $model->updateDeletedBy($model);
                $model->save();
            }
        });

        if (method_exists(static::class, 'restoring'))
            static::restoring(fn ($model) => $model->clearDeletedBy($model));

        static::saving(fn ($model) => $model->updateUserBy($model));

        // $events = [
        //     'creating', // updateUserBy
        //     'created',
        //     'updating', // updateUserBy
        //     'updated',
        //     'deleting', // updateUserBy
        //     'deleted',
        //     'retrieved',
        //     'saving', // updateUserBy
        //     'saved',
        //     'restoring',
        //     'restored',
        //     'replicating',
        // ];
    }

    /**
     * Update the model's update multitenance User.
     *
     * @return bool
     */
    public function touchUserBy()
    {
        if (!$this->usesUserBy()) {
            return false;
        }

        $this->updateUserBy($this);

        return $this->save();
    }

    /**
     * Update the creation and update multitenance User.
     *
     * @return void
     */
    public function updateUserBy($model)
    {
        if (!$this->usesUserBy()) return;
        $model->addUserByAttributes($model);
        $userid = $model->freshUserBy();

        $updatedByColumn = $model->getUpdatedByColumn();

        if (!is_null($updatedByColumn) && !$model->isDirty($updatedByColumn) && !empty($userid)) {
            $model->setUpdatedBy($userid);
        }

        $createdAtColumn = $model->getCreatedByColumn();

        if (!$model->exists && !is_null($createdAtColumn) && !empty($userid)) {
            $model->setCreatedBy($userid);
        }
    }
    /**
     * Update the deleted multitenance User.
     *
     * @return void
     */
    public function updateDeletedBy($model)
    {
        /** @var Model $model */
        if (!$this->usesUserBy()) return;
        $model->addDeletedByAttributes($model);
        $userid = $model->freshUserBy();

        $deletedByColumn = $model->getDeletedByColumn();
        if (!is_null($deletedByColumn) && !$model->isDirty($deletedByColumn) && !empty($userid)) {
            $model->setDeletedBy($userid);
        }
    }
    /**
     * clear the deleted multitenance User.
     *
     * @return void
     */
    public function clearDeletedBy($model)
    {
        /** @var Model $model */
        $this->setDeletedBy(null);
    }

    public function addUserByAttributes($model)
    {
        $updatedByColumn = $model->getUpdatedByColumn();
        $model->fillable = !in_array($updatedByColumn, $model->fillable)
            ?  array_merge($model->fillable, [$updatedByColumn])
            : $model->fillable;
        $createdAtColumn = $model->getCreatedByColumn();
        $model->fillable = !in_array($createdAtColumn, $model->fillable)
            ?  array_merge($model->fillable, [$createdAtColumn])
            : $model->fillable;
    }
    public function addDeletedByAttributes($model)
    {
        /** @var Model $model */
        $deletedByColumn = $model->getDeletedByColumn();

        if (!in_array($deletedByColumn, $model->getFillable())) $model->fillable([$deletedByColumn]);
    }

    /**
     * Set the value of the "created by" attribute.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function setCreatedBy($value)
    {
        $this->setAttribute($this->getCreatedByColumn(), $value);
        return $this;
    }

    /**
     * Set the value of the "updated by" attribute.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function setUpdatedBy($value)
    {
        $this->setAttribute($this->getUpdatedByColumn(), $value);
        return $this;
    }
    /**
     * Set the value of the "updated by" attribute.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function setDeletedBy($value)
    {
        $this->setAttribute($this->getDeletedByColumn(), $value);
        return $this;
    }

    /**
     * Get a fresh user id for the model.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function freshUserBy()
    {
        if (auth()->check()) {
            // authenticated user
            return auth()->id();
        } else {
            // not authenticated and user instance
            return null;
        }
    }

    /**
     * Determine if the model uses mutitenance.
     *
     * @return bool
     */
    public function usesUserBy()
    {
        return isset($this->userBy) ? $this->userBy : config('simple-mutitenancy.enable', true);
    }

    /**
     * Get the name of the "created by" column.
     *
     * @return string|null
     */
    public function getCreatedByColumn()
    {
        return config('mutitenancy.column_created', 'created_by');
    }

    /**
     * Get the name of the "updated by" column.
     *
     * @return string|null
     */
    public function getUpdatedByColumn()
    {
        return config('mutitenancy.column_updated', 'updated_by');
    }
    /**
     * Get the name of the "deleted by" column.
     *
     * @return string|null
     */
    public function getDeletedByColumn()
    {
        return config('mutitenancy.column_deleted', 'deleted_by');
    }

    /**
     * Get the fully qualified "created by" column.
     *
     * @return string|null
     */
    public function getQualifiedCreatedByColumn()
    {
        return $this->qualifyColumn($this->getCreatedByColumn());
    }

    /**
     * Get the fully qualified "updated by" column.
     *
     * @return string|null
     */
    public function getQualifiedUpdatedByColumn()
    {
        return $this->qualifyColumn($this->getUpdatedByColumn());
    }
    /**
     * Get the fully qualified "deleted by" column.
     *
     * @return string|null
     */
    public function getQualifiedDeletedByColumn()
    {
        return $this->qualifyColumn($this->getDeletedByColumn());
    }
}
