<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
        /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'messages';

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
        'client_id, '
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
        'speech_text' => 'string'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

     // Relations ...
    /**
     * Get the speech's content.
     */
    public function content()
    {
        return $this->morphOne(Content::class, 'childable');
    }

    /**
     * Get the speech's response questionnaire
     */
    public function response()
    {
        return $this->hasOne(ResponseQuestionnaire::class);
    }
}
