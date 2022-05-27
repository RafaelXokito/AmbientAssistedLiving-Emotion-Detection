<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property float  $accuracylimit
 * @property float  $duration
 * @property string $emotion_name
 */
class Emotionsnotification extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'emotionsnotifications';

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
        'accuracylimit', 'client_id', 'duration', 'emotion_name'
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
        'accuracylimit' => 'float', 'duration' => 'double', 'emotion_name' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [

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
