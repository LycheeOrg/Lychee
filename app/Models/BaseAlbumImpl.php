<?php

namespace App\Models;

use App\Contracts\BaseModelAlbum;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\HasTimeBasedID;
use App\Models\Extensions\UTCBasedTimes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class BaseAlbumImpl.
 *
 * This class contains the shared implementation of {@link \App\Models\Album}
 * and {@link \App\Models\TagAlbum} which normally would be but into a common
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
 * {@link \Illuminate\Database\Eloquent\Model} and are a composites which
 * use this class as "building block".
 * This means instead of inheriting from this class, {@link \App\Models\Album}
 * and {@link \App\Models\TagAlbum} hold a reference to the implementation of
 * their "parent" class.
 * Basically, the architecture looks like this
 *
 *        +---------+             +----------------+
 *        |  Model  |             | <<interface>>  |
 *        +---------+             | BaseModelAlbum |
 *             ^ ^ ^              +----------------+
 *             | | \                ^           ^
 *             | |  \               |           |
 *             | \   \--------------|------\    |
 *             |  \-------------\   |       \   |
 *             |               +-------+     \  |
 *             |               | Album |      | |
 *     +---------------+ <---X +-------+    +----------+
 *     | BaseAlbumImpl |                    | TagAlbum |
 *     +---------------+ <----------------X +----------+
 *
 * (Note: A sideways arrow with an X, i.e. <-----X, shall denote a composite.)
 * All child classes and the this class extend
 * {@link \Illuminate\Database\Eloquent\Model}, because they map to a single
 * DB table.
 * All methods and properties which are common to any sort of peristable
 * album is declared in the interface {@link \App\Contracts\BaseModelAlbum}
 * and thus {@link \App\Models\Album} and {@link \App\Models\TagAlbum}
 * realize it.
 * However, for any method which is implemented identically for all
 * child classes and thus would normally be defined in a true parent class,
 * the child classes forward the call to this class via the composite.
 * For this reason, this class is called `BaseAlbumImpl` like _implementation_.
 * Also note, that this class does not realize
 * {@link \App\Contracts\BaseModelAlbum} intentionally.
 * The interface {@link \App\Contracts\BaseModelAlbum} requires methods from
 * albums which this class cannot implement reasonably, because the
 * implementation depends on the specific sub-type of album and thus must
 * be implemented by the child classes.
 * For example, every album contains photos and thus must provide
 * {@link \App\Contracts\BaseAlbum::$photos}, but the way how an album
 * defines its collection of photos is specific for the album.
 * Normally, a proper parent class would use abstract methods for these cases,
 * but this class is not a proper parent class (it just provides an
 * implementation of it) and we need this class to be instantiable.
 *
 * @property int            $id
 * @property Carbon         $created_at
 * @property Carbon         $updated_at
 * @property string         $album_type
 * @property BaseModelAlbum $child_class
 * @property string         $title
 * @property string|null    $description
 * @property bool           $public
 * @property bool           $full_photo
 * @property bool           $viewable             // rename, on different layer of this application this attribute goes by different names: "hidden", "need_direct_link", etc.
 * @property bool           $downloadable
 * @property bool           $share_button_visible
 * @property bool           $nsfw
 * @property int            $owner_id
 * @property User           $owner
 * @property Collection     $shared_with
 * @property string|null    $password
 * @property string|null    $sorting_col
 * @property string|null    $sorting_order
 */
class BaseAlbumImpl extends Model
{
	use HasAttributesPatch;
	use HasTimeBasedID;
	use UTCBasedTimes;
	use HasBidirectionalRelationships;

	const INHERITANCE_RELATION_NAME = 'child_class';
	const INHERITANCE_DISCRIMINATOR_COL_NAME = 'album_type';
	const INHERITANCE_ID_COL_NAME = 'id';

	protected $table = 'base_albums';

	/**
	 * Indicates if the model's primary key is auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'public' => 'boolean',
		'full_photo' => 'boolean',
		'viewable' => 'boolean',
		'downloadable' => 'boolean',
		'share_button_visible' => 'boolean',
		'nsfw' => 'boolean',
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
	 * Returns the relationship between an album and all users which whom
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

	/**
	 * Returns the polymorphic relationship which refers to the child
	 * class.
	 *
	 * @return MorphTo
	 */
	public function child_class(): MorphTo
	{
		return $this->morphTo(
			self::INHERITANCE_RELATION_NAME,
			self::INHERITANCE_DISCRIMINATOR_COL_NAME,
			self::INHERITANCE_ID_COL_NAME,
			self::INHERITANCE_ID_COL_NAME
		);
	}
}
