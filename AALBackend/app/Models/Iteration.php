<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $macaddress
 * @property string $emotion_name
 * @property int    $created_at
 */
class Iteration extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'iterations';

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
        'macaddress', 'type', 'client_id', 'created_at', 'emotion_name', 'usage_id'
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
        'macaddress' => 'string', 'type' => 'string', 'created_at' => 'timestamp', 'emotion_name' => 'string', 'usage_id' => 'string'
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

    /**
     * Get the emotion associated with the iteration.
     */
    public function emotion()
    {
        return $this->belongsTo(Emotion::class, 'emotion_name', 'name');
    }
    /**
     * Get the client associated with the iteration.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
    // Relations ...
    /**
     * Get the frames associated with the emotion.
     */
    public function contents()
    {
        return $this->hasMany(Content::class, 'iteration_id', 'id');
    }
}
