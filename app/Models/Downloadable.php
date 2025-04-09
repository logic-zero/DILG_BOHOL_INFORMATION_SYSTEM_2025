<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Downloadable extends Model
{
    use LogsActivity;

    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'link',
        'outcome_area',
        'program',
        'file'
    ];

    protected $table = "downloadables";

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has successfully created a new downloadable with title '{$model->title}'.");
        });

        static::updated(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has updated the downloadable with title '{$model->title}'.");
        });

        static::deleted(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has deleted the downloadable with title '{$model->title}'.");
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('downloadable')
            ->setDescriptionForEvent(fn(string $eventName) => "Downloadable has been {$eventName} successfully.");
    }
}