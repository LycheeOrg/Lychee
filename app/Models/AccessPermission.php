<?php

namespace App\Models;

use App\Constants\AccessPermissionConstants as APC;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
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
 */
class AccessPermission extends Model
{
	use UTCBasedTimes;
	use HasAttributesPatch;
	use ThrowsConsistentExceptions;
	// use HasBidirectionalRelationships;
	/** @phpstan-use UseFixedQueryBuilder<Photo> */
	use UseFixedQueryBuilder;

	// protected string $table = 'access_permissions';

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'user_id' => 'integer',
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
		APC::IS_LINK_REQUIRED,
		APC::GRANTS_FULL_PHOTO_ACCESS,
		APC::GRANTS_DOWNLOAD,
		APC::GRANTS_UPLOAD,
		APC::GRANTS_EDIT,
		APC::GRANTS_DELETE,
		APC::PASSWORD,
	];

	/**
	 * Returns the relationship between an AccessPermission and its associated album.
	 *
	 * @return BelongsTo
	 */
	public function album(): BelongsTo
	{
		return $this->belongsTo(BaseAlbumImpl::class, 'base_album_id', 'id');
	}

	/**
	 * Returns the relationship between an AccessPermission and its applied User.
	 *
	 * @return BelongsTo
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
}