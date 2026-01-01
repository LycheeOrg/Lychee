<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Constants\AccessPermissionConstants as APC;
use App\Models\Builders\UserGroupBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserGroup.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $description
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class UserGroup extends Model
{
	use HasFactory;
	use UTCBasedTimes;
	use ThrowsConsistentExceptions {
		delete as parentDelete;
	}
	use ToArrayThrowsNotImplemented;

	/**
	 * @var list<string> the attributes that are mass assignable
	 */
	protected $fillable = [
		'name',
		'description',
	];

	/**
	 * @return array<string,string>
	 */
	protected function casts(): array
	{
		return [
			'id' => 'integer',
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
		];
	}

	protected $hidden = [];

	/**
	 * Create a new Eloquent query builder for the model.
	 *
	 * @param BaseBuilder $query
	 *
	 * @return UserGroupBuilder
	 */
	public function newEloquentBuilder($query): UserGroupBuilder
	{
		return new UserGroupBuilder($query);
	}

	/**
	 * Returns the relationship between an album and its associated permissions.
	 *
	 * @return hasMany<AccessPermission,$this>
	 */
	public function access_permissions(): HasMany
	{
		return $this->hasMany(AccessPermission::class, APC::USER_GROUP_ID, 'id');
	}

	/**
	 * Returns the relationship between an album and all users with whom
	 * this album is shared.
	 *
	 * @return BelongsToMany<User,$this>
	 */
	public function users(): BelongsToMany
	{
		return $this->belongsToMany(
			User::class,
			'users_user_groups',
			'user_group_id',
			'user_id',
		)->withPivot('role', 'created_at')->orderBy('role', 'asc')->orderBy('username', 'asc');
	}
}
