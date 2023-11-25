<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireType extends Model
{
    use HasFactory;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "questionnaire_types";


    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'name';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'points_min', 'points_max', 'name', 'display_name', 'questionnairable_model_name'
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
        'name' => 'string',
        'display_name' => 'string',   
        'points_min' => 'float',  
        'points_max' => 'float',
        'questionnairable_model_name' => 'string'
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

     /**
     * Get the mappings associated with the questionnaire type.
     */
    public function result_mappings()
    {
        return $this->hasMany(QuestionnaireResultMapping::class, 'questionnaire', 'name');
    }

     /**
     * Get the questions associated with the questionnaire type.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'questionnaire', 'name');
    }
}
