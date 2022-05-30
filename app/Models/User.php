<?php

namespace App\Models;

use App\Exceptions\ModelDBException;
use App\Facades\AccessControl;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use Carbon\Exceptions\InvalidFormatException;
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
 * @property string|null                                           $password
 * @property string|null                                           $email
 * @property bool                                                  $may_upload
 * @property bool                                                  $is_locked
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
	use UseFixedQueryBuilder;

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
	 */
	public function delete(): bool
	{
		$now = Carbon::now();
		$newOwnerID = AccessControl::id();

		/** @var HasMany[] $ownershipRelations */
		$ownershipRelations = [$this->photos(), $this->albums()];

		foreach ($ownershipRelations as $relation) {
			// We must also update the `updated_at` column of the related
			// models in case clients have cached these models.
			$relation->update([
				$relation->getForeignKeyName() => $newOwnerID,
				$relation->getRelated()->getUpdatedAtColumn() => $relation->getRelated()->fromDateTime($now),
			]);
		}

		$this->shared()->delete();

		return $this->parentDelete();
	}
}
