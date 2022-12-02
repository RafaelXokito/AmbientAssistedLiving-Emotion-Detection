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
        'name', 'path'
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
       'name' => 'string', 'path' => 'string'
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
    public $timestamps = false;

    // Scopes...

    // Functions ...

    // Relations ...

    /**
     * Get the frame's content.
     */
    public function content()
    {
        return $this->morphOne(Content::class, 'contentChild');
    }
}
