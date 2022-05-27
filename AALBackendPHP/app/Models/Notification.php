<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string  $title
 * @property string  $content
 * @property string  $emotion_name
 * @property int     $created_at
 * @property float   $duration
 * @property float   $accuracy
 * @property boolean $notificationseen
 */
class Notification extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'created_at', 'duration', 'emotion_name', 'notificationseen', 'client_id', 'accuracy'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'title' => 'string', 'content' => 'string', 'created_at' => 'timestamp', 'duration' => 'float', 'emotion_name' => 'string', 'notificationseen' => 'boolean', 'accuracy' => 'float'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    // Scopes...

    // Functions ...

    // Relations ...
}
