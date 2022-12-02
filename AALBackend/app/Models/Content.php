<?php

namespace App\Models;

use App\Models\Emotion;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contents';

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
        'accuracy', 'createdate','iteration_id', 'updated_at', 'emotion_name','childable_id','childable_type'
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
        'userable_type' => 'string', 'accuracy' => 'float', 'createdate' => 'timestamp', 'emotion_name' => 'string', 'updated_at' => 'timestamp'
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

    /**
     * Get the iteration associated with the content.
     */
    public function iteration()
    {
        return $this->belongsTo(Iteration::class, 'iteration_id', 'id');
    }

    /**
     * Get the emotion associated with the content.
     */
    public function emotion()
    {
        return $this->belongsTo(Emotion::class, 'emotion_name', 'name');
    }
    /**
     * Get the classifications associated with the content.
     */
    public function classifications()
    {
        return $this->hasMany(Classification::class, 'content_id', 'id');
    }

    /**
     * Get the parent content model (user or post).
     */
    public function childable()
    {
        return $this->morphTo();
    }
}
