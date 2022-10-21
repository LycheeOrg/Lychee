<?php

namespace App\Models;

use App\Contracts\AbstractAlbum;
use App\Contracts\HasRandomID;
use App\DTO\AlbumProtectionPolicy;
use App\DTO\PhotoSortingCriterion;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\HasRandomIDAndLegacyTimeBasedID;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use App\Policies\AlbumPolicy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
 * {@link \App\Contracts\AbstractAlbum::$photos}, but the way how an album
 * defines its collection of photos is specific for the album.
 * Normally, a proper parent class would use abstract methods for these cases,
 * but this class is not a proper parent class (it just provides an
 * implementation of it) and we need this class to be instantiable.
 *
 * @property string                     $id
 * @property int                        $legacy_id
 * @property Carbon                     $created_at
 * @property Carbon                     $updated_at
 * @property string                     $title
 * @property string|null                $description
 * @property int                        $owner_id
 * @property User                       $owner
 * @property bool                       $is_public
 * @property bool                       $is_link_required
 * @property bool                       $is_nsfw
 * @property bool                       $grants_access_full_photo
 * @property bool                       $grants_download
 * @property Collection                 $shared_with
 * @property string|null                $password
 * @property bool                       $has_password
 * @property PhotoSortingCriterion|null $sorting
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

	protected $table = 'base_albums';

	/**
	 * @var string The type of the primary key
	 */
	protected $keyType = \App\Contracts\HasRandomID::ID_TYPE;

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
		HasRandomID::LEGACY_ID_NAME => null,
		'created_at' => null,
		'updated_at' => null,
		'title' => null, // Sic! `title` is actually non-nullable, but using `null` here forces the caller to actually set a title before saving.
		'description' => null,
		'owner_id' => 0,
		'sorting_col' => null,
		'sorting_order' => null,
		// Security attributes
		'is_nsfw' => false,
		'is_public' => false,
		'is_link_required' => false,
		'password' => null,
		// Permissions
		'grants_access_full_photo' => true,
		'grants_download' => false,
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'id' => HasRandomID::ID_TYPE,
		HasRandomID::LEGACY_ID_NAME => HasRandomID::LEGACY_ID_TYPE,
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'is_public' => 'boolean',
		'is_link_required' => 'boolean',
		'is_nsfw' => 'boolean',
		'owner_id' => 'integer',
	];

	/**
	 * @var array<int,string> The list of attributes which exist as columns of the DB
	 *                        relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		HasRandomID::LEGACY_ID_NAME,
		'owner_id',
		'owner',
		'password',
		'sorting_col',   // serialize DTO `order` instead
		'sorting_order', // serialize DTO `order` instead

		// Security attributes are hidden because provided by the DTO AlbumProtectionPolicy
		'is_public',
		'is_link_required',

		// Permissions are hidden because they will eventually be replaced by an external table
		// and are provided by the AlbumRightsDTO
		'grants_download',
		'grants_access_full_photo',
	];

	/**
	 * @var array<int,string> The list of "virtual" attributes which do not exist as
	 *                        columns of the DB relation but which shall be appended to
	 *                        JSON from accessors
	 */
	protected $appends = [
		'has_password',
		'sorting',
		'policies',
	];

	/**
	 * The relationships that should always be eagerly loaded by default.
	 */
	protected $with = ['owner'];

	/**
	 * Returns the relationship between an album and its owner.
	 *
	 * @return BelongsTo
	 */
	public function owner(): BelongsTo
	{
		return $this->belongsTo('App\Models\User', 'owner_id', 'id');
	}

	/**
	 * Returns the relationship between an album and all users with whom
	 * this album is shared.
	 *
	 * @return BelongsToMany
	 */
	public function shared_with(): BelongsToMany
	{
		return $this->belongsToMany(
			'App\Models\User',
			'user_base_album',
			'base_album_id',
			'user_id'
		);
	}

	protected function getGrantsFullPhotoAttribute(bool $value): bool
	{
		if ($this->is_public) {
			return $value;
		} else {
			return Configs::getValueAsBool('full_photo');
		}
	}

	protected function getIsDownloadableAttribute(bool $value): bool
	{
		if ($this->is_public) {
			return $value;
		} else {
			return Configs::getValueAsBool('downloadable');
		}
	}

	protected function getHasPasswordAttribute(): bool
	{
		return $this->password !== null && $this->password !== '';
	}

	protected function getSortingAttribute(): ?PhotoSortingCriterion
	{
		$sortingColumn = $this->attributes['sorting_col'];
		$sortingOrder = $this->attributes['sorting_order'];

		return ($sortingColumn === null || $sortingOrder === null) ?
			null :
			new PhotoSortingCriterion($sortingColumn, $sortingOrder);
	}

	protected function setSortingAttribute(?PhotoSortingCriterion $sorting): void
	{
		$this->attributes['sorting_col'] = $sorting?->column;
		$this->attributes['sorting_order'] = $sorting?->order;
	}

	/**
	 * Provide the policy attributes for said album.
	 *
	 * @return AlbumProtectionPolicy|null
	 */
	protected function getPoliciesAttribute(): AlbumProtectionPolicy|null
	{
		// Provide the policies if the user can edit.
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this]) ? AlbumProtectionPolicy::ofBaseAlbum($this) : null;
	}

	public function toArray(): array
	{
		$result = parent::toArray();
		if (Auth::check()) {
			$result['owner_name'] = $this->owner->name;
		}

		return $result;
	}
}
