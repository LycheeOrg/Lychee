<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Constants\AccessPermissionConstants as APC;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Builders\UserBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Models\Extensions\UTCBasedTimes;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Laragear\WebAuthn\WebAuthnAuthentication;
use Laragear\WebAuthn\WebAuthnData;
use function Safe\mb_convert_encoding;

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
 * @property int                                                   $quota_kb
 * @property string|null                                           $description
 * @property string|null                                           $note
 * @property string|null                                           $token
 * @property string|null                                           $remember_token
 * @property Collection<int,BaseAlbumImpl>                         $albums
 * @property Collection<int,OauthCredential>                       $oauthCredentials
 * @property DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property Collection<int,BaseAlbumImpl>                         $shared
 * @property Collection<int,Photo>                                 $photos
 * @property int|null                                              $photos_count
 * @property Collection<int,WebAuthnCredential>                    $webAuthnCredentials
 * @property int|null                                              $web_authn_credentials_count
 *
 * @method static UserBuilder|User addSelect($column)
 * @method static UserBuilder|User join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static UserBuilder|User joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static UserBuilder|User leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static UserBuilder|User newModelQuery()
 * @method static UserBuilder|User newQuery()
 * @method static UserBuilder|User orderBy($column, $direction = 'asc')
 * @method static UserBuilder|User query()
 * @method static UserBuilder|User select($columns = [])
 * @method static UserBuilder|User whereCreatedAt($value)
 * @method static UserBuilder|User whereEmail($value)
 * @method static UserBuilder|User whereId($value)
 * @method static UserBuilder|User whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static UserBuilder|User whereMayAdministrate($value)
 * @method static UserBuilder|User whereMayEditOwnSettings($value)
 * @method static UserBuilder|User whereMayUpload($value)
 * @method static UserBuilder|User whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static UserBuilder|User wherePassword($value)
 * @method static UserBuilder|User whereRememberToken($value)
 * @method static UserBuilder|User whereToken($value)
 * @method static UserBuilder|User whereUpdatedAt($value)
 * @method static UserBuilder|User whereUsername($value)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements WebAuthnAuthenticatable
{
	/** @phpstan-use HasFactory<\Database\Factories\UserFactory> */
	use HasFactory;
	use Notifiable;
	use WebAuthnAuthentication;
	use UTCBasedTimes;
	use ThrowsConsistentExceptions {
		delete as parentDelete;
	}
	use ToArrayThrowsNotImplemented;

	/**
	 * @var list<string> the attributes that are mass assignable
	 */
	protected $fillable = [
		'username',
		'password',
		'email',
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
		'quota_kb' => 'integer',
	];

	protected $hidden = [];

	/**
	 * We always want to load the user groups when loading a user.
	 * So that we can use the groups to determine the permissions without having to do intersection in the db.
	 *
	 * Furthermore, that way it is also provided when using Auth::user()
	 *
	 * @var list<string>
	 */
	protected $with = [
		'user_groups',
	];

	/**
	 * Create a new Eloquent query builder for the model.
	 *
	 * @param BaseBuilder $query
	 *
	 * @return UserBuilder
	 */
	public function newEloquentBuilder($query): UserBuilder
	{
		return new UserBuilder($query);
	}

	/**
	 * Return the albums owned by the user.
	 *
	 * @return HasMany<BaseAlbumImpl,$this>
	 */
	public function albums(): HasMany
	{
		return $this->hasMany(BaseAlbumImpl::class, 'owner_id', 'id');
	}

	/**
	 * Return the photos owned by the user.
	 *
	 * @return HasMany<Photo,$this>
	 */
	public function photos(): HasMany
	{
		return $this->hasMany(Photo::class, 'owner_id', 'id');
	}

	/**
	 * Return the Oauth credentials owned by the user.
	 *
	 * @return HasMany<OauthCredential,$this>
	 */
	public function oauthCredentials(): HasMany
	{
		return $this->hasMany(OauthCredential::class, 'user_id', 'id');
	}

	/**
	 * Used by Larapass.
	 *
	 * @return string
	 */
	public function username(): string
	{
		return mb_convert_encoding($this->username, 'UTF-8');
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
		$ownership_relations = [$this->photos(), $this->albums()];
		$has_any = false;

		foreach ($ownership_relations as $relation) {
			$has_any = $has_any || $relation->count() > 0;
		}

		if ($has_any) {
			// only try update relations if there are any to allow deleting users from migrations (relations are moved before deleting)
			$now = Carbon::now();
			$new_owner_id = Auth::id() ?? throw new UnauthenticatedException();

			foreach ($ownership_relations as $relation) {
				// We must also update the `updated_at` column of the related
				// models in case clients have cached these models.
				$relation->update([
					$relation->getForeignKeyName() => $new_owner_id,
					$relation->getRelated()->getUpdatedAtColumn() => $relation->getRelated()->fromDateTime($now),
				]);
			}
		}

		AccessPermission::query()->where(APC::USER_ID, '=', $this->id)->delete();
		WebAuthnCredential::query()->where('authenticatable_id', '=', $this->id)->delete();

		return $this->parentDelete();
	}

	/**
	 * Returns displayable data to be used to create WebAuthn Credentials.
	 * The default function use email and name, however in Lyche the email is optional.
	 */
	public function webAuthnData(): WebAuthnData
	{
		return WebAuthnData::make($this->email ?? ($this->name . '#' . request()->httpHost()), $this->name);
	}

	/**
	 * Returns the relationship between an album and all users with whom
	 * this album is shared.
	 *
	 * @return BelongsToMany<UserGroup,$this>
	 */
	public function user_groups(): BelongsToMany
	{
		return $this->belongsToMany(
			UserGroup::class,
			'users_user_groups',
			'user_id',
			'user_group_id',
		)->withPivot('role', 'created_at')->orderBy('role', 'asc')->orderBy('name', 'asc');
	}
}