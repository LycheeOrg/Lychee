<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Constants\AccessPermissionConstants as APC;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Builders\AccessPermissionBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AccessPermission.
 *
 * @property int                             $id
 * @property int|null                        $user_id
 * @property string|null                     $base_album_id
 * @property bool                            $is_link_required
 * @property string|null                     $password
 * @property bool                            $grants_full_photo_access
 * @property bool                            $grants_download
 * @property bool                            $grants_upload
 * @property bool                            $grants_edit
 * @property bool                            $grants_delete
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Models\BaseAlbumImpl|null  $album
 * @property \App\Models\User|null           $user
 *
 * @method static AccessPermissionBuilder|AccessPermission addSelect($column)
 * @method static AccessPermissionBuilder|AccessPermission join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static AccessPermissionBuilder|AccessPermission joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static AccessPermissionBuilder|AccessPermission leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static AccessPermissionBuilder|AccessPermission newModelQuery()
 * @method static AccessPermissionBuilder|AccessPermission newQuery()
 * @method static AccessPermissionBuilder|AccessPermission orderBy($column, $direction = 'asc')
 * @method static AccessPermissionBuilder|AccessPermission query()
 * @method static AccessPermissionBuilder|AccessPermission select($columns = [])
 * @method static AccessPermissionBuilder|AccessPermission whereBaseAlbumId($value)
 * @method static AccessPermissionBuilder|AccessPermission whereCreatedAt($value)
 * @method static AccessPermissionBuilder|AccessPermission whereGrantsDelete($value)
 * @method static AccessPermissionBuilder|AccessPermission whereGrantsDownload($value)
 * @method static AccessPermissionBuilder|AccessPermission whereGrantsEdit($value)
 * @method static AccessPermissionBuilder|AccessPermission whereGrantsFullPhotoAccess($value)
 * @method static AccessPermissionBuilder|AccessPermission whereGrantsUpload($value)
 * @method static AccessPermissionBuilder|AccessPermission whereId($value)
 * @method static AccessPermissionBuilder|AccessPermission whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static AccessPermissionBuilder|AccessPermission whereIsLinkRequired($value)
 * @method static AccessPermissionBuilder|AccessPermission whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static AccessPermissionBuilder|AccessPermission wherePassword($value)
 * @method static AccessPermissionBuilder|AccessPermission whereUpdatedAt($value)
 * @method static AccessPermissionBuilder|AccessPermission whereUserId($value)
 *
 * @mixin \Eloquent
 */
class AccessPermission extends Model
{
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;
	/** @phpstan-use HasFactory<\Database\Factories\AccessPermissionFactory> */
	use HasFactory;

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		APC::USER_ID => 'integer',
		APC::IS_LINK_REQUIRED => 'boolean',
		APC::GRANTS_FULL_PHOTO_ACCESS => 'boolean',
		APC::GRANTS_DOWNLOAD => 'boolean',
		APC::GRANTS_UPLOAD => 'boolean',
		APC::GRANTS_EDIT => 'boolean',
		APC::GRANTS_DELETE => 'boolean',
	];

	/**
	 * allow these properties to be mass assigned.
	 */
	protected $fillable = [
		APC::USER_ID,
		APC::BASE_ALBUM_ID,
		APC::IS_LINK_REQUIRED,
		APC::GRANTS_FULL_PHOTO_ACCESS,
		APC::GRANTS_DOWNLOAD,
		APC::GRANTS_UPLOAD,
		APC::GRANTS_EDIT,
		APC::GRANTS_DELETE,
		APC::PASSWORD,
	];

	/**
	 * @param $query
	 *
	 * @return AccessPermissionBuilder
	 */
	public function newEloquentBuilder($query): AccessPermissionBuilder
	{
		return new AccessPermissionBuilder($query);
	}

	/**
	 * Returns the relationship between an AccessPermission and its associated album.
	 *
	 * @return BelongsTo<BaseAlbumImpl,$this>
	 */
	public function album(): BelongsTo
	{
		return $this->belongsTo(BaseAlbumImpl::class, 'base_album_id', 'id');
	}

	/**
	 * Returns the relationship between an AccessPermission and its applied User.
	 *
	 * @return BelongsTo<User,$this>
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	/**
	 * Given an AccessPermission, duplicate its reccord.
	 * - Password is NOT transfered
	 * - base_album_id is NOT transfered.
	 *
	 * @param AccessPermission $accessPermission
	 *
	 * @return AccessPermission
	 */
	public static function ofAccessPermission(AccessPermission $accessPermission): self
	{
		return $accessPermission->replicate([APC::PASSWORD, APC::BASE_ALBUM_ID]);
	}

	/**
	 * Return a new Public sharing permission with defaults.
	 *
	 * @return AccessPermission
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public static function ofPublic(): self
	{
		return new AccessPermission([
			APC::IS_LINK_REQUIRED => false,
			APC::GRANTS_FULL_PHOTO_ACCESS => Configs::getValueAsBool('grants_full_photo_access'),
			APC::GRANTS_DOWNLOAD => Configs::getValueAsBool('grants_download'),
			APC::GRANTS_UPLOAD => false,
			APC::GRANTS_EDIT => false,
			APC::GRANTS_DELETE => false,
			APC::PASSWORD => null,
		]);
	}

	/**
	 * Return a new permission set associated to a specific userId.
	 *
	 * @param int $userId
	 *
	 * @return AccessPermission
	 */
	public static function withGrantFullPermissionsToUser(int $userId): self
	{
		return new AccessPermission([
			APC::USER_ID => $userId,
			APC::GRANTS_FULL_PHOTO_ACCESS => true,
			APC::GRANTS_DOWNLOAD => true,
			APC::GRANTS_UPLOAD => true,
			APC::GRANTS_EDIT => true,
			APC::GRANTS_DELETE => true,
		]);
	}
}