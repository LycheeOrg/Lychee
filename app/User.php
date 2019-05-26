<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * App\User.
 *
 * @property int                                                   $id
 * @property string                                                $username
 * @property string                                                $password
 * @property int                                                   $upload
 * @property int                                                   $lock
 * @property string|null                                           $remember_token
 * @property Carbon|null                                           $created_at
 * @property Carbon|null                                           $updated_at
 * @property Collection|Album[]                                    $albums
 * @property DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property Collection|Album[]                                    $shared
 *
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLock($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUpload($value)
 * @method static Builder|User whereUsername($value)
 * @mixin Eloquent
 */
class User extends Authenticatable
{
	use Notifiable;

	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = [
		'username',
		'password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 */
	protected $hidden = [
		'password',
		'remember_token',
		'created_at',
		'updated_at',
	];

	protected $casts = [
		'upload' => 'int',
		'lock' => 'int',
	];

	/**
	 * Return the albums owned by the user.
	 *
	 * @return HasMany
	 */
	public function albums()
	{
		return $this->hasMany('App\Album', 'owner_id', 'id');
	}

	/**
	 * Return the albums shared to the user.
	 *
	 * @return BelongsToMany
	 */
	public function shared()
	{
		return $this->belongsToMany('App\Album', 'user_album', 'user_id', 'album_id');
	}
}
