<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Field_Officer extends Model
{
    use HasFactory, LogsActivity;

    protected $primaryKey = 'id';
    protected $fillable =
    [
        'municipality_id',
        'profile_img',
        'fname',
        'mid_initial',
        'lname',
        'position',
        'cluster',
    ];

    protected $table = "field_officers";

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function municipality() {
        return $this->belongsTo('App\Models\Municipality');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has successfully added a new field officer: {$model->fname} {$model->lname}, Position: '{$model->position}'.");
        });

        static::updated(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has updated the field officer entry for {$model->fname} {$model->lname}, Position: '{$model->position}'.");
        });

        static::deleted(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has deleted the field officer entry for {$model->fname} {$model->lname}, Position: '{$model->position}'.");
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('field_officer')
            ->setDescriptionForEvent(fn(string $eventName) => "Field officer entry has been {$eventName} successfully.");
    }
}
