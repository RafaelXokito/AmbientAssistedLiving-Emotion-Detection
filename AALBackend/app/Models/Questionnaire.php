<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "questionnaires";

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
    protected $fillable = ['questionnairable_type','questionnairable_id','client_id','points','created_at','updated_at'];
    
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
        'questionnairable_type' => 'string', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'
    ];

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

     // Relations
    public function questionnairable() {
        return $this->morphTo();
    }

    /**
 * Get the responses to the questionnaire
 */
    public function responses(){
        return $this->hasMany(ResponseQuestionnaire::class, 'questionnaire_id','id');
    }

    /**
     * Get the client associated with the questionnaire.
     */
    public function client(){
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
