<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RegulationMechanism extends Model
{
   /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'regulation_mechanisms';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';
 /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_name'
    ];
    public $timestamps = false;

    // Relations ...
    /**
     * Get the regulation mechanisms associated with the mechanism.
     */
    public function emotionRegulationMechanisms()
    {
        return $this->hasMany(EmotionRegulationMechanism::class, 'regulation_mechanism', 'name');
    }
}
