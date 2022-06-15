<?php

namespace App\Models;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property int    $created_at
 * @property string $scope
 * @property string $email
 * @property string $name
 * @property string $password
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, MustVerifyEmail, SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

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
        'userable_type', 'created_at', 'email', 'name', 'password', 'userable_id', 'updated_at', 'deleted_at', 'notifiable'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'confirmation_code',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'userable_type' => 'string', 'created_at' => 'timestamp', 'email' => 'string', 'name' => 'string', 'password' => 'string', 'updated_at' => 'timestamp', 'deleted_at' => 'timestamp', 'email_verified_at' => 'datetime', 'notifiable' => 'boolean',
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

    // Scopes...

    // Functions
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relations
    public function userable() {
        return $this->morphTo();
    }
}
