<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "questions";

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = "id";

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['number','question','questionnaire'];
    
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
        'number' => 'int', 'question' => 'string', 'questionnaire' => 'string'
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

      // Relations ...
    /**
    * Get the questionnaire type associated with the mapping.
    */
   public function questionnaire()
   {
       return $this->belongsTo(QuestionnaireType::class, 'questionnaire', 'name');
   }
}
