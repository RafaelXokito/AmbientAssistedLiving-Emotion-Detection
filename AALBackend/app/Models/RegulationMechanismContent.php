<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegulationMechanismContent extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'regulation_mechanisms_contents';

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
        'regulation_mechanism', 'content_type', 'file_path', 'text'
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
    * Get the regulation mechanism associated with the the content
    */
   public function regulationMechanism()
   {
       return $this->belongsTo(RegulationMechanism::class, 'regulation_mechanism', 'id');
   }
}
