<?php

namespace App\Models\Extensions;

use App\Constants\RandomID;
use App\Contracts\Models\AbstractAlbum;
use App\Contracts\Models\HasRandomID;
use App\DTO\PhotoSortingCriterion;
use App\Models\AccessPermission;
use App\Models\BaseAlbumImpl;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;

/**
 * Interface BaseAlbum.
 *
 * This is the common interface for all albums which can be created and
 * deleted by a user at runtime or more accurately which can be persisted
 * to the DB.
 *
 * @property int                              $legacy_id
 * @property Carbon                           $created_at
 * @property Carbon                           $updated_at
 * @property string|null                      $description
 * @property bool                             $is_nsfw
 * @property int                              $owner_id
 * @property User                             $owner
 * @property Collection<int,AccessPermission> $access_permissions
 * @property Carbon|null                      $min_taken_at
 * @property Carbon|null                      $max_taken_at
 * @property PhotoSortingCriterion|null       $sorting
 * @property BaseAlbumImpl                    $base_class
 */
abstract class BaseAlbum extends Model implements AbstractAlbum, HasRandomID
{
	use HasBidirectionalRelationships;
	use ForwardsToParentImplementation, ThrowsConsistentExceptions {
		ForwardsToParentImplementation::delete insteadof ThrowsConsistentExceptions;
	}

	/**
	 * @var string The type of the primary key
	 */
	protected $keyType = RandomID::ID_TYPE;

	/**
	 * Indicates if the model's primary key is auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * {@inheritDoc}
	 */
	public function base_class(): BelongsTo
	{
		return $this->belongsTo(BaseAlbumImpl::class, 'id', 'id');
	}

	/**
	 * Returns the relationship between an album and its owner.
	 *
	 * @return BelongsTo
	 */
	public function owner(): BelongsTo
	{
		return $this->base_class->owner();
	}

	/**
	 * Returns the relationship between an album and all users with whom
	 * this album is shared.
	 *
	 * @return BelongsToMany
	 */
	public function shared_with(): BelongsToMany
	{
		return $this->base_class->shared_with();
	}

	/**
	 * Returns the relationship between an album and its associated permissions.
	 *
	 * @return HasMany
	 */
	public function access_permissions(): HasMany
	{
		return $this->base_class->access_permissions();
	}

	/**
	 * Returns the relationship between an album and its associated current user permissions.
	 *
	 * @return AccessPermission|null
	 */
	public function current_user_permissions(): AccessPermission|null
	{
		return $this->base_class->current_user_permissions();
	}

	/**
	 * Returns the relationship between an album and its associated public permissions.
	 *
	 * @return AccessPermission|null
	 */
	public function public_permissions(): AccessPermission|null
	{
		return $this->base_class->public_permissions();
	}

	abstract public function photos(): Relation;

	/**
	 * Returns the criterion acc. to which **photos** inside the album shall be sorted.
	 *
	 * @return PhotoSortingCriterion the attribute acc. to which **photos** inside the album shall be sorted
	 */
	public function getEffectiveSorting(): PhotoSortingCriterion
	{
		return $this->sorting ?? PhotoSortingCriterion::createDefault();
	}
}
