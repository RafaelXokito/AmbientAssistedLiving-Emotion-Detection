<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultiModalEmotion extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'multimodal_emotions';

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
        'emotion_name', 'client_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'updated_at' => 'timestamp','created_at' => 'timestamp', 'emotion_name' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'updated_at', 'created_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    /**
     * Get the emotion associated with the multimodal emotion.
     */
    public function emotion()
    {
        return $this->belongsTo(Emotion::class, 'emotion_name', 'name');
    }

       /**
     * Get the client associated with the multimodal emotion.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
