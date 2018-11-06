<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Albums owned
     */
    public function albums() {
        return $this->hasMany('App\Album','owner_id','id');
    }

    /**
     * Albums visible (shared)
     */
    public function shared () {
        return $this->belongsToMany('App\Album','user_album','user_id','album_id');
    }
}
