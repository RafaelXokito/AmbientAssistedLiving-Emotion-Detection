<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $category
 */
class Emotion extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'emotions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'category'
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
        'name' => 'string', 'category' => 'string'
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
    /**
     * Get the frames associated with the emotion.
     */
    public function frames()
    {
        return $this->hasMany(Frame::class, 'emotion_name', 'name');
    }
    /**
     * Get the iterations associated with the emotion.
     */
    public function iterations()
    {
        return $this->hasMany(Iteration::class, 'emotion_name', 'name');
    }
    /**
     * Get the classifications associated with the emotion.
     */
    public function classifications()
    {
        return $this->hasMany(Classification::class, 'emotion_name', 'name');
    }
    /**
     * Get the emotion notifications associated with the emotion.
     */
    public function emotionNotifications()
    {
        return $this->hasMany(EmotionsNotification::class, 'emotion_name', 'name');
    }
    /**
     * Get the notifications associated with the emotion.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'emotion_name', 'name');
    }
}
