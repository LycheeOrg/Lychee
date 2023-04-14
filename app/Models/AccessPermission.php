<?php

namespace App\Models;

use App\Constants\AccessPermissionConstants as APC;
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
 * @property int                             $owner_id
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
		'owner_id' => 'integer',
		APC::IS_LINK_REQUIRED => 'boolean',
		APC::GRANTS_FULL_PHOTO_ACCESS => 'boolean',
		APC::GRANTS_DOWNLOAD => 'boolean',
		APC::GRANTS_UPLOAD => 'boolean',
		APC::GRANTS_EDIT => 'boolean',
		APC::GRANTS_DELETE => 'boolean',
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
	 * Returns the relationship between an AccessPermission and its owner.
	 *
	 * @return BelongsTo
	 */
	public function owner(): BelongsTo
	{
		return $this->belongsTo(User::class, 'owner_id', 'id');
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
}