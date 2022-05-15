<?php

namespace App\Models;

use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use DarkGhostHunter\Larapass\Contracts\WebAuthnAuthenticatable;
use DarkGhostHunter\Larapass\WebAuthnAuthentication;
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
 * @property Carbon                                                $created_at
 * @property Carbon                                                $updated_at
 * @property string                                                $username
 * @property string                                                $display_name
 * @property string|null                                           $password
 * @property string|null                                           $email
 * @property bool                                                  $may_upload
 * @property bool                                                  $is_locked
 * @property string|null                                           $remember_token
 * @property Collection<BaseAlbumImpl>                             $albums
 * @property DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property Collection<BaseAlbumImpl>                             $shared
 */
class User extends Authenticatable implements WebAuthnAuthenticatable
{
	use Notifiable;
	use WebAuthnAuthentication;
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;
	use UseFixedQueryBuilder;

	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = [
		'username',
		'password',
		'email',
		'display_name',
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
		'id' => 'integer',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'may_upload' => 'boolean',
		'is_locked' => 'boolean',
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

	/**
	 * Accessor for `display_name` property.
	 *
	 * @param ?string $value the raw value from the database passed in by
	 *                       the Eloquent framework
	 *
	 * @return string the display name
	 */
	public function getDisplayNameAttribute(?string $value): string
	{
		if (empty($value) || (Configs::get_value('ldap_enabled', '0') == 0)) {
			return $this->name();
		}

		return $value;
	}

	/**
	 * Accessor for `is_locked` property.
	 *
	 * The `is_locked` attribute determines whether users may change their
	 * passwords.
	 *
	 * @param bool $value the raw value from the database passed in by
	 *                    the Eloquent framework
	 *
	 * @return bool the effective value of the `is_locked` attribute
	 */
	public function getIsLockedAttribute(bool $value): bool
	{
		// If LDAP is enabled, every user is locked
		return $value || Configs::get_value('ldap_enabled', '0');
	}
}
