<?php

namespace App\Models;

use App\Casts\MustNotSetCast;
use App\Constants\RandomID;
use App\Contracts\Models\HasRandomID;
use App\DTO\AlbumProtectionPolicy;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\HasRandomIDAndLegacyTimeBasedID;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

/**
 * Class BaseAlbumImpl.
 *
 * This class contains the shared implementation of {@link \App\Models\Album}
 * and {@link \App\Models\TagAlbum} which normally would be put into a common
 * parent class.
 * However, Eloquent does not provide mapping of class inheritance to table
 * inheritance.
 * (For an introduction into this topic see
 * [Martin Fowler](https://martinfowler.com/eaaCatalog/classTableInheritance.html)
 * and
 * [Doctrine Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/2.9/reference/inheritance-mapping.html#class-table-inheritance)).
 * The main obstacle is that Eloquent cannot handle attributes of a child
 * class which are either stored in the table of the parent class (if the
 * attributes are inherited) or stored in the table the child class (if the
 * attributes are specific to the child class).
 * Hence, we take the second best approach here: using composites.
 * The actual child classes {@link \App\Models\Album} and
 * {@link \App\Models\TagAlbum} both extend
 * {@link \Illuminate\Database\Eloquent\Model} and are composites which
 * use this class as "building block".
 * This means instead of inheriting from this class, {@link \App\Models\Album}
 * and {@link \App\Models\TagAlbum} hold a reference to the implementation of
 * their "parent" class.
 * Basically, the architecture looks like this
 *
 *       +---------+             +-----------------+
 *       |  Model  |             |  <<interface>>  |
 *       +---------+             |    BaseAlbum    |
 *          ^ ^ ^                +-----------------+
 *          | | \                   ^           ^
 *          | |  \                  |           |
 *          | \   \-----------------|------\    |
 *          |  \----------------\   |       \   |
 *          |                  +-------+     \  |
 *          |                  | Album |      | |
 *     +---------------+ <---X +-------+    +----------+
 *     | BaseAlbumImpl |                    | TagAlbum |
 *     +---------------+ <----------------X +----------+
 *
 * (Note: A sideways arrow with an X, i.e. <-----X, shall denote a composite.)
 * All child classes and this class extend
 * {@link \Illuminate\Database\Eloquent\Model}, because they map to a single
 * DB table.
 * All methods and properties which are common to any sort of persistable
 * album is declared in the interface {@link \App\Contracts\BaseAlbum}
 * and thus {@link \App\Models\Album} and {@link \App\Models\TagAlbum}
 * realize it.
 * However, for any method which is implemented identically for all
 * child classes and thus would normally be defined in a true parent class,
 * the child classes forward the call to this class via the composite.
 * For this reason, this class is called `BaseAlbumImpl` like _implementation_.
 * Also note, that this class does not realize
 * {@link \App\Contracts\BaseAlbum} intentionally.
 * The interface {@link \App\Contracts\BaseAlbum} requires methods from
 * albums which this class cannot implement reasonably, because the
 * implementation depends on the specific sub-type of album and thus must
 * be implemented by the child classes.
 * For example, every album contains photos and thus must provide
 * {@link \App\Contracts\Models\AbstractAlbum::$photos}, but the way how an album
 * defines its collection of photos is specific for the album.
 * Normally, a proper parent class would use abstract methods for these cases,
 * but this class is not a proper parent class (it just provides an
 * implementation of it) and we need this class to be instantiable.
 *
 * @property string                           $id
 * @property int                              $legacy_id
 * @property Carbon                           $created_at
 * @property Carbon                           $updated_at
 * @property string                           $title
 * @property string|null                      $description
 * @property int                              $owner_id
 * @property User                             $owner
 * @property bool                             $is_nsfw
 * @property PhotoSortingCriterion|null       $sorting
 * @property string|null                      $sorting_col
 * @property string|null                      $sorting_order
 * @property AlbumProtectionPolicy            $policy
 * @property Collection<int,AccessPermission> $access_permissions
 * @property AccessPermission|null            $current_permissions
 * @property AccessPermission|null            $public_permissions
 */
class BaseAlbumImpl extends Model implements HasRandomID
{
	use HasAttributesPatch;
	use HasRandomIDAndLegacyTimeBasedID;
	use ThrowsConsistentExceptions;
	use UTCBasedTimes;
	use HasBidirectionalRelationships;
	/** @phpstan-use UseFixedQueryBuilder<BaseAlbumImpl> */
	use UseFixedQueryBuilder;
	use ToArrayThrowsNotImplemented;

	protected $table = 'base_albums';

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
	 * The model's attributes.
	 *
	 * We must list all attributes explicitly here, otherwise the attributes
	 * of a new model will accidentally be set on the child class.
	 * The trait {@link \App\Models\Extensions\ForwardsToParentImplementation}
	 * only works properly, if it knows which attributes belong to the parent
	 * class and which attributes belong to the child class.
	 *
	 * @var array<string, mixed>
	 */
	protected $attributes = [
		'id' => null,
		RandomID::LEGACY_ID_NAME => null,
		'created_at' => null,
		'updated_at' => null,
		'title' => null, // Sic! `title` is actually non-nullable, but using `null` here forces the caller to actually set a title before saving.
		'description' => null,
		'owner_id' => 0,
		'sorting_col' => null,
		'sorting_order' => null,
		// "Security" attributes
		'is_nsfw' => false,
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'id' => RandomID::ID_TYPE,
		RandomID::LEGACY_ID_NAME => RandomID::LEGACY_ID_TYPE,
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'is_nsfw' => 'boolean',
		'owner_id' => 'integer',
		'policy' => MustNotSetCast::class,
	];

	/**
	 * The relationships that should always be eagerly loaded by default.
	 */
	protected $with = ['owner', 'access_permissions', 'current_user_permissions', 'public_permissions'];

	/**
	 * Returns the relationship between an album and its owner.
	 *
	 * @return BelongsTo
	 */
	public function owner(): BelongsTo
	{
		return $this->belongsTo(User::class, 'owner_id', 'id');
	}

	/**
	 * Returns the relationship between an album and its associated permissions.
	 *
	 * @return hasMany
	 */
	public function access_permissions(): hasMany
	{
		return $this->hasMany(AccessPermission::class, 'base_album_id', 'id');
	}

	/**
	 * Returns the relationship between an album and its associated current user permissions.
	 *
	 * @return HasOne
	 */
	public function current_user_permissions(): HasOne
	{
		return $this->access_permissions()->one()->where('user_id', '=', Auth::id());
	}

	/**
	 * Returns the relationship between an album and its associated public permissions.
	 *
	 * @return HasOne
	 */
	public function public_permissions(): HasOne
	{
		return $this->access_permissions()->one()->whereNull('user_id');
	}

	protected function getSortingAttribute(): ?PhotoSortingCriterion
	{
		$sortingColumn = $this->attributes['sorting_col'];
		$sortingOrder = $this->attributes['sorting_order'];

		return ($sortingColumn === null || $sortingOrder === null) ?
			null :
			new PhotoSortingCriterion(
				ColumnSortingType::from($sortingColumn),
				OrderSortingType::from($sortingOrder));
	}

	protected function setSortingAttribute(?PhotoSortingCriterion $sorting): void
	{
		$this->attributes['sorting_col'] = $sorting?->column->value;
		$this->attributes['sorting_order'] = $sorting?->order->value;
	}

	/**
	 * Provide the policy attributes for said album.
	 *
	 * @return AlbumProtectionPolicy
	 */
	protected function getPolicyAttribute(): AlbumProtectionPolicy
	{
		return AlbumProtectionPolicy::ofBaseAlbumImplementation($this);
	}
}
