<?php

namespace App\Models;

use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UTCBasedTimes;
use DarkGhostHunter\Larapass\Contracts\WebAuthnAuthenticatable;
use DarkGhostHunter\Larapass\WebAuthnAuthentication;
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
 * App\Models\User.
 *
 * @property int                                                   $id
 * @property string                                                $username
 * @property string                                                $password
 * @property string|null                                           $email
 * @property int                                                   $upload
 * @property int                                                   $lock
 * @property string|null                                           $remember_token
 * @property Carbon                                                $created_at
 * @property Carbon                                                $updated_at
 * @property Collection                                            $albums
 * @property DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property Collection                                            $shared
 *
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLock($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUpload($value)
 * @method static Builder|User whereUsername($value)
 */
class User extends Authenticatable implements WebAuthnAuthenticatable
{
	use Notifiable;
	use WebAuthnAuthentication;
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;

	const FRIENDLY_MODEL_NAME = 'user';

	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = [
		'username',
		'password',
		'email',
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
	public function albums(): HasMany
	{
		return $this->hasMany('App\Models\BaseAlbumImpl', 'owner_id', 'id');
	}

	/**
	 * Return the albums shared to the user.
	 *
	 * @return BelongsToMany
	 */
	public function shared(): BelongsToMany
	{
		return $this->belongsToMany(
			BaseAlbumImpl::class,
			'user_base_album',
			'user_id',
			'base_album_id'
		);
	}

	public function is_admin(): bool
	{
		return $this->id == 0;
	}

	public function can_upload(): bool
	{
		return $this->id == 0 || $this->upload;
	}

	// ! Used by Larapass
	public function username(): string
	{
		return utf8_encode($this->username);
	}

	// ! Used by Larapass
	public function name(): string
	{
		return ($this->id == 0) ? 'Admin' : $this->username;
	}

	protected function friendlyModelName(): string
	{
		return self::FRIENDLY_MODEL_NAME;
	}
}
