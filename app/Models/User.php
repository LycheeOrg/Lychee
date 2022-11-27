<?php

namespace App\Models;

use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;

/**
 * App\Models\User.
 *
 * @property int                                                   $id
 * @property Carbon                                                $created_at
 * @property Carbon                                                $updated_at
 * @property string                                                $username
 * @property string|null                                           $password
 * @property string|null                                           $email
 * @property bool                                                  $may_administrate
 * @property bool                                                  $may_upload
 * @property bool                                                  $may_edit_own_settings
 * @property string|null                                           $token
 * @property bool                                                  $has_token
 * @property string|null                                           $remember_token
 * @property Collection<BaseAlbumImpl>                             $albums
 * @property DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property Collection<BaseAlbumImpl>                             $shared
 * @property Collection<Photo>                                     $photos
 */
class User extends Authenticatable implements WebAuthnAuthenticatable
{
	use Notifiable;
	use WebAuthnAuthentication;
	use UTCBasedTimes;
	use ThrowsConsistentExceptions {
		delete as parentDelete;
	}
	/** @phpstan-use UseFixedQueryBuilder<User> */
	use UseFixedQueryBuilder;

	/**
	 * @var string[] the attributes that are mass assignable
	 */
	protected $fillable = [
		'username',
		'password',
		'email',
	];

	/**
	 * @var array<int,string> the attributes that should be hidden for arrays
	 */
	protected $hidden = [
		'password',
		'remember_token',
		'created_at',
		'updated_at',
		'token',

		/**
		 * We do not forward those to the front end: they are provided by {@link \App\DTO\UserWithCapabilitiesDTO}.
		 * We do not need to inform every user on Lychee who can upload etc.
		 */
		'may_administrate',
		'may_upload',
		'may_edit_own_settings',
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'id' => 'integer',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'may_administrate' => 'boolean',
		'may_upload' => 'boolean',
		'may_edit_own_settings' => 'boolean',
	];

	/**
	 * @var array
	 */
	protected $appends = [
		'has_token',
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
	 * Return the photos owned by the user.
	 *
	 * @return HasMany
	 */
	public function photos(): HasMany
	{
		return $this->hasMany('App\Models\Photo', 'owner_id', 'id');
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

	/**
	 * Used by Larapass.
	 *
	 * @return string
	 */
	public function username(): string
	{
		return utf8_encode($this->username);
	}

	/**
	 * Used by Larapass since 2022-09-21.
	 *
	 * @return string
	 */
	public function getNameAttribute(): string
	{
		// If strings starts by '$2y$', it is very likely that it's a blowfish hash.
		return substr($this->username, 0, 4) === '$2y$' ? 'Admin' : $this->username;
	}

	/**
	 * Deletes a user from the DB and re-assigns ownership of albums and photos
	 * to the currently authenticated user.
	 *
	 * For efficiency reasons the methods performs a mass-update without
	 * hydrating the actual models.
	 *
	 * @return bool always true
	 *
	 * @throws ModelDBException
	 * @throws InvalidFormatException
	 * @throws UnauthenticatedException
	 */
	public function delete(): bool
	{
		/** @var HasMany[] $ownershipRelations */
		$ownershipRelations = [$this->photos(), $this->albums()];
		$hasAny = false;

		foreach ($ownershipRelations as $relation) {
			$hasAny = $hasAny || $relation->count() > 0;
		}

		if ($hasAny) {
			// only try update relations if there are any to allow deleting users from migrations (relations are moved before deleting)
			$now = Carbon::now();
			$newOwnerID = Auth::id() ?? throw new UnauthenticatedException();

			foreach ($ownershipRelations as $relation) {
				// We must also update the `updated_at` column of the related
				// models in case clients have cached these models.
				$relation->update([
					$relation->getForeignKeyName() => $newOwnerID,
					$relation->getRelated()->getUpdatedAtColumn() => $relation->getRelated()->fromDateTime($now),
				]);
			}
		}

		$this->shared()->delete();
		// TODO delete webauthn credentials

		return $this->parentDelete();
	}

	/**
	 * @return bool
	 */
	public function getHasTokenAttribute(): bool
	{
		return $this->token !== null;
	}
}
