<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PDMUs extends Model
{
    use LogsActivity;

    protected $primaryKey = 'id';
    protected $fillable =
    [
        'profile_img',
        'fname',
        'mid_initial',
        'lname',
        'position',
    ];

    protected $table = "pdmus";

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
                ->log("The user '{$userName}' has successfully added a new member to the PDMUs: {$model->fname} {$model->lname}, Position: '{$model->position}'.");
        });

        static::updated(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has updated the PDMUs entry for {$model->fname} {$model->lname}, Position: '{$model->position}'.");
        });

        static::deleted(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has deleted the PDMUs entry for {$model->fname} {$model->lname}, Position: '{$model->position}'.");
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('pdmus')
            ->setDescriptionForEvent(fn(string $eventName) => "PDMUs entry has been {$eventName} successfully.");
    }
}
