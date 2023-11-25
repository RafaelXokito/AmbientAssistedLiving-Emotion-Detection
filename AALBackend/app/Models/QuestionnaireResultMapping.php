<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireResultMapping extends Model
{
    use HasFactory;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "questionnaire_result_mappings";


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
        'points_min', 'points_max', 'message', 'points_max_inclusive', 'questionnaire'
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
        'message' => 'string',
        'questionnaire' => 'string', 
        'points_max_inclusive' => 'boolean',  
        'points_min' => 'float',  
        'points_max' => 'float'
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
