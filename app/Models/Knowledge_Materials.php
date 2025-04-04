<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Knowledge_Materials extends Model
{
    use LogsActivity;

    protected $primaryKey = 'id';
    protected $fillable =
    [
        'title',
        'link',
        'file',
        'date'
    ];

    protected $table = "knowledge_materials";

    protected $casts = [
        'created_at' => 'datetime',
        'date' => 'date',
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
                ->log("The user '{$userName}' has successfully added a new Knowledge Material: '{$model->title}'.");
        });

        static::updated(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has updated the Knowledge Material: '{$model->title}'.");
        });

        static::deleted(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has deleted the Knowledge Material: '{$model->title}'.");
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('knowledge_material')
            ->setDescriptionForEvent(fn(string $eventName) => "Knowledge Material entry has been {$eventName} successfully.");
    }
}
