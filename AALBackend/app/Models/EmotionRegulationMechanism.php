<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmotionRegulationMechanism extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'emotions_regulation_mechanisms';

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
        'client_id', 'regulation_mechanism', 'emotion', 'is_default'
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
        'regulation_mechanism' => 'string', 'emotion' => 'string', 'is_default' => 'boolean'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];
    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    // Relations ...
    /**
     * Get the emotion associated with the emotion regulation mechanism.
     */
    public function emotionToRegulate()
    {
        return $this->belongsTo(Emotion::class, 'emotion', 'name');
    }

    /**
     * Get the regulation mechanism associated with the emotion regulation mechanism.
    */
    public function regulationMechanism()
    {
        return $this->belongsTo(RegulationMechanism::class, 'regulation_mechanism', 'name');
    }

    /**
     * Get the client associated with the emotion regulation mechanism.
    */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
