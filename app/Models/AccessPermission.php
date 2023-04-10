<?php

namespace App\Models;

use App\Models\Extensions\FixedQueryBuilder;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AccessPermission.
 *
 * @property int                             $id
 * @property int                             $owner_id
 * @property int|null                        $user_id
 * @property string|null                     $base_album_id
 * @property int                             $is_link_required
 * @property string|null                     $password
 * @property int                             $grants_full_photo_access
 * @property int                             $grants_download
 * @property int                             $grants_upload
 * @property int                             $grants_edit
 * @property int                             $grants_delete
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static FixedQueryBuilder|AccessPermission addSelect($column)
 * @method static FixedQueryBuilder|AccessPermission join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static FixedQueryBuilder|AccessPermission leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static FixedQueryBuilder|AccessPermission newModelQuery()
 * @method static FixedQueryBuilder|AccessPermission newQuery()
 * @method static FixedQueryBuilder|AccessPermission orderBy($column, $direction = 'asc')
 * @method static FixedQueryBuilder|AccessPermission query()
 * @method static FixedQueryBuilder|AccessPermission select($columns = [])
 * @method static FixedQueryBuilder|AccessPermission whereBaseAlbumId($value)
 * @method static FixedQueryBuilder|AccessPermission whereCreatedAt($value)
 * @method static FixedQueryBuilder|AccessPermission whereGrantsDelete($value)
 * @method static FixedQueryBuilder|AccessPermission whereGrantsDownload($value)
 * @method static FixedQueryBuilder|AccessPermission whereGrantsEdit($value)
 * @method static FixedQueryBuilder|AccessPermission whereGrantsFullPhotoAccess($value)
 * @method static FixedQueryBuilder|AccessPermission whereGrantsUpload($value)
 * @method static FixedQueryBuilder|AccessPermission whereId($value)
 * @method static FixedQueryBuilder|AccessPermission whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static FixedQueryBuilder|AccessPermission whereIsLinkRequired($value)
 * @method static FixedQueryBuilder|AccessPermission whereOwnerId($value)
 * @method static FixedQueryBuilder|AccessPermission wherePassword($value)
 * @method static FixedQueryBuilder|AccessPermission whereUpdatedAt($value)
 * @method static FixedQueryBuilder|AccessPermission whereUserId($value)
 *
 * @mixin \Eloquent
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
		'is_public' => 'boolean',
		'grants_full_photo_access' => 'boolean',
		'grants_download' => 'boolean',
		'grants_upload' => 'boolean',
		'grants_edit' => 'boolean',
		'grants_delete' => 'boolean',
	];
}