<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property float  $accuracy
 * @property int    $createdate
 * @property int    $updated_at
 * @property string $emotion_name
 * @property string $name
 * @property string $path
 */
class Frame extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'frames';

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
        'accuracy', 'createdate', 'emotion_name', 'iteration_id', 'name', 'path', 'updated_at'
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
        'accuracy' => 'float', 'createdate' => 'timestamp', 'emotion_name' => 'string', 'name' => 'string', 'path' => 'string', 'updated_at' => 'timestamp'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'createdate', 'updated_at'
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
