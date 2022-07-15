<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $emotion_name
 * @property float  $accuracy
 */
class Classification extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'classifications';

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
        'frame_id', 'emotion_name', 'accuracy'
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
        'emotion_name' => 'string', 'accuracy' => 'float'
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
     * Get the emotion associated with the classification.
     */
    public function emotion()
    {
        return $this->belongsTo(Emotion::class, 'emotion_name', 'name');
    }
    /**
     * Get the classifications associated with the classification.
     */
    public function frame()
    {
        return $this->belongsTo(Frame::class, 'frame_id', 'id');
    }
}