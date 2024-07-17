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
    protected $primaryKey = 'id';
    
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
        'client_id', 'description'
    ];

    public $timestamps = true;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];


     // Relations ...
    /**
    * Get the client associated with the message.
    */
   public function client()
   {
       return $this->belongsTo(Client::class, 'client_id', 'id');
   }

    // Relations ...
    /**
     * Get the emotions regulation mechanisms associated with the mechanism.
     */
    public function emotionRegulationMechanisms()
    {
        return $this->hasMany(EmotionRegulationMechanism::class, 'regulation_mechanism', 'id');
    }

    // Relations ...
    /**
     * Get the regulation mechanisms associated with the mechanism.
     */
    public function regulationMechanismsContents()
    {
        return $this->hasMany(EmotionRegulationContent::class, 'regulation_mechanism', 'id');
    }
}
