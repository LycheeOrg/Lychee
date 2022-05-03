<?php

namespace App\Models\Extensions;

use App\Contracts\AbstractAlbum;
use App\Contracts\HasRandomID;
use App\DTO\PhotoSortingCriterion;
use App\Models\BaseAlbumImpl;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;

/**
 * Interface BaseAlbum.
 *
 * This is the common interface for all albums which can be created and
 * deleted by a user at runtime or more accurately which can be persisted
 * to the DB.
 *
 * @property int                        $legacy_id
 * @property Carbon                     $created_at
 * @property Carbon                     $updated_at
 * @property string|null                $description
 * @property bool                       $is_nsfw
 * @property bool                       $grants_full_photo
 * @property int                        $owner_id
 * @property User                       $owner
 * @property Collection                 $shared_with
 * @property bool                       $requires_link
 * @property string|null                $password
 * @property bool                       $has_password
 * @property Carbon|null                $min_taken_at
 * @property Carbon|null                $max_taken_at
 * @property PhotoSortingCriterion|null $sorting
 * @property BaseAlbumImpl              $base_class
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
	protected $keyType = HasRandomID::ID_TYPE;

	/**
	 * Indicates if the model's primary key is auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * Returns the relationship between this model and the implementation
	 * of the "parent" class.
	 *
	 * @return BelongsTo
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

	abstract public function photos(): Relation;

	public function toArray(): array
	{
		return array_merge(parent::toArray(), $this->base_class->toArray());
	}

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
