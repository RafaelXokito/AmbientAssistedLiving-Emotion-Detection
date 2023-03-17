<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseGeriatricQuestionnaire extends Model
{
     /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "responses_geriatric_questionnaire";

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
    protected $fillable = ['questionnaire_id','response','why','question','created_at','updated_at'];

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
    protected $casts = ['created_at' => 'timestamp' , 'updated_at' => 'timestamp'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at'
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
     * Get the geriatric questionnaire associated with the response.
     */
    public function geriatric_questionnaire()
    {
        return $this->belongsTo(GeriatricQuestionnaire::class, 'questionnaire_id', 'id');
    }

    /**
     * Get the speech associated with the response.
     */
    public function speech()
    {
        return $this->belongsTo(Speech::class);
    }
}
