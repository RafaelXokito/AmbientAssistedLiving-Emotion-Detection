<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $contact
 * @property int    $birthdate
 */
class Client extends Model
{

    use SoftDeletes;


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'clients';

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
        'contact', 'birthdate', 'administrator_id', 'is_active'
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
        'contact' => 'string', 'birthdate' => 'timestamp', 'is_active' => 'boolean'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'birthdate'
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
     * Get the administrator associated with the client.
     */
    public function administrator()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id', 'id')->withTrashed();
    }
    /**
     * Get the iterations associated with the emotion.
     */
    public function iterations()
    {
        return $this->hasMany(Iteration::class, 'client_id', 'id');
    }
    /**
     * Get the logs associated with the emotion.
     */
    public function logs()
    {
        return $this->hasMany(Log::class, 'client_id', 'id');
    }
    /**
     * Get the emotion expressions associated with the emotion.
     */
    public function emotionExpressions()
    {
        return $this->hasMany(EmotionExpression::class, 'client_id', 'id');
    }
    /**
     * Get the emotion notifications associated with the emotion.
     */
    public function emotionNotifications()
    {
        return $this->hasMany(EmotionsNotification::class, 'client_id', 'id');
    }
    /**
     * Get the notifications associated with the emotion.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'client_id', 'id');
    }
    /**
     * Get the user associated with the client.
     */
    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    /**
     * Get the multi-modal emotions associated with the client.
     */
    public function multiModalEmotions()
    {
        return $this->hasMany(MultiModalEmotion::class, 'client_id', 'id');
    }
}
