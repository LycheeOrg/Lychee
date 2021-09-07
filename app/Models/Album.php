<?php

namespace App\Models;

use App\Contracts\BaseModelAlbum;
use App\Facades\AccessControl;
use App\Models\Extensions\ForwardsToParentImplementation;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\NodeTrait;
use App\Models\Extensions\Thumb;
use App\Relations\HasManyBidirectionally;
use App\Relations\HasManyChildAlbums;
use App\Relations\HasManyPhotosRecursively;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Kalnoy\Nestedset\QueryBuilder as NSQueryBuilder;

/**
 * Class Album.
 *
 * @property int|null    $parent_id
 * @property Album|null  $parent
 * @property Collection  $children
 * @property Collection  $all_photos
 * @property string      $license
 * @property int|null    $cover_id
 * @property Photo|null  $cover
 * @property Carbon|null $min_taken_at
 * @property Carbon|null $max_taken_at
 * @property int         $_lft
 * @property int         $_rgt
 */
class Album extends Model implements BaseModelAlbum
{
	use NodeTrait;
	use HasBidirectionalRelationships;
	use ForwardsToParentImplementation {
		delete as private forwardDelete;
	}

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
	 * of a new model will accidentally be set on the parent class.
	 * The trait {@link \App\Models\Extensions\ForwardsToParentImplementation}
	 * only works properly, if it knows which attributes belong to the parent
	 * class and which attributes belong to the child class.
	 *
	 * @var array
	 */
	protected $attributes = [
		'id' => null,
		'parent_id' => null,
		'license' => 'none',
		'cover_id' => null,
		'_lft' => null,
		'_rgt' => null,
	];

	protected $casts = [
		'min_taken_at' => 'datetime',
		'max_taken_at' => 'datetime',
		'cover_id' => 'integer',
		'parent_id' => 'integer',
	];

	/**
	 * @var string[] The list of attributes which exist as columns of the DB
	 *               relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'base_class', // don't serialize base class as a relation, the attributes of the base class are flatly merged into the JSON result
		'cover',      // instead of cover, serialize thumb
		'_lft',
		'_rgt',
		'parent',     // avoid infinite recursions
		'all_photos', // never serialize recursive child photos of an album, even if the relation is loaded
	];

	/**
	 * @var string[] The list of "virtual" attributes which do not exist as
	 *               columns of the DB relation but which shall be appended to
	 *               JSON from accessors
	 */
	protected $appends = ['thumb'];

	/**
	 * The relationships that should always be eagerly loaded by default.
	 */
	protected $with = ['cover'];

	/**
	 * This method is called by the framework after the model has been
	 * booted.
	 *
	 * This method alters the default query builder for this model and
	 * adds a "scope" to the query builder in order to add the "virtual"
	 * columns `max_taken_at` and `min_taken_at` to every query.
	 */
	protected static function booted()
	{
		parent::booted();
		// Normally "scopes" are used to restrict the result of the query
		// to a particular subset through adding additional WHERE-clauses
		// to the default query.
		// However, "scopes" can be used to manipulate the query in any way.
		// Here we add to additional "virtual" columns to the query.
		static::addGlobalScope('add_minmax_taken_at', function (Builder $builder) {
			$builder->addSelect([
				'max_taken_at' => Photo::query()
					->select('taken_at')
					->leftJoin('albums as a', 'a.id', '=', 'album_id')
					->whereColumn('a._lft', '>=', 'albums._lft')
					->whereColumn('a._rgt', '<=', 'albums._rgt')
					->whereNotNull('taken_at')
					->orderBy('taken_at', 'desc')
					->limit(1),
				'min_taken_at' => Photo::query()
					->select('taken_at')
					->leftJoin('albums as a', 'a.id', '=', 'album_id')
					->whereColumn('a._lft', '>=', 'albums._lft')
					->whereColumn('a._rgt', '<=', 'albums._rgt')
					->whereNotNull('taken_at')
					->orderBy('taken_at', 'asc')
					->limit(1),
			]);
		});
	}

	/**
	 * Returns the relationship between this model and the implementation
	 * of the "parent" class.
	 *
	 * @return BelongsTo
	 */
	public function base_class(): BelongsTo
	{
		return $this->belongsTo(BaseModelAlbumImpl::class, 'id', 'id');
	}

	/**
	 * Return the relationship between this album and photos which are
	 * direct children of this album.
	 *
	 * @return HasManyBidirectionally
	 */
	public function photos(): HasManyBidirectionally
	{
		return $this->hasManyBidirectionally(Photo::class);
	}

	/**
	 * Returns the relationship between this album and all photos incl.
	 * photos which are recursive children of this album.
	 *
	 * @return HasManyPhotosRecursively
	 */
	public function all_photos(): HasManyPhotosRecursively
	{
		return new HasManyPhotosRecursively($this);
	}

	protected function getThumbAttribute(): ?Thumb
	{
		if ($this->cover_id) {
			return Thumb::createFromPhoto($this->cover);
		}
		// Note, `all_photos` already applies a security filter and
		// only returns photos which are accessible by the current
		// user
		return Thumb::createFromPhotoRelation(
			$this->all_photos(), $this->sorting_col, $this->sorting_order
		);
	}

	/**
	 * Return the relationship between an album and its sub albums.
	 *
	 * Note: Actually, the return type should be non-nullable.
	 * However, {@link \App\SmartAlbums\BareSmartAlbum} extends this class and
	 * {@link \App\SmartAlbums\SmartAlbum::children()} cannot return an
	 * correctly instantiated object of `HasMany` but must return `null`,
	 * because a `SmartAlbum` is not a real Eloquent model and does not exist
	 * as a database entity.
	 *
	 * @return HasManyChildAlbums
	 */
	public function children(): HasManyChildAlbums
	{
		return new HasManyChildAlbums($this);
	}

	/**
	 * Return the relationship between an album and its cover.
	 *
	 * @return HasOne
	 */
	public function cover(): HasOne
	{
		return $this->hasOne(Photo::class, 'id', 'cover_id');
	}

	/**
	 * Return the relationship between an album and its parent.
	 *
	 * @return BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parent_id', 'id');
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
	 * Returns the relationship between an album and all users which whom
	 * this album is shared.
	 *
	 * @return BelongsToMany
	 */
	public function shared_with(): BelongsToMany
	{
		return $this->base_class->shared_with();
	}

	protected function getLicenseAttribute(string $value): string
	{
		if ($value === 'none') {
			return Configs::get_value('default_license');
		}

		return $value;
	}

	/**
	 * Checks whether this album is truly and completely empty.
	 *
	 * Note, that one must not use the relations {@link Album::photos()} and
	 * {@link Album::children()} to check for emptiness.
	 * These relations filter the results with respect to the access rights of
	 * the current user.
	 * In other words, {@link Album::photos()} and {@link Album::children()}
	 * may appear to be empty, but the album is not, because the album is
	 * still parent to photos and sub-albums invisible for the current user.
	 *
	 * @return bool true if this album is completely empty
	 */
	public function isEmpty(): bool
	{
		$photosCount = Photo::query()->where('album_id', '=', $this->id)->count();
		$albumCount = Album::query()->where('parent_id', '=', $this->id)->count();

		return ($photosCount + $albumCount) === 0;
	}

	public function toArray(): array
	{
		$result = parent::toArray();
		$result['has_albums'] = !$this->isLeaf();

		// The client expect the relation "children" to be named "albums".
		// Rename it
		if (key_exists('children', $result)) {
			$result['albums'] = $result['children'];
			unset($result['children']);
		}

		return array_merge($result, $this->base_class->toArray());
	}

	/**
	 * Recursively deletes the album incl. potential sub-albums and photos.
	 *
	 * Note, this method only deletes albums and photos which are owned by
	 * the current user.
	 * If the album is not empty (after all sub-albums and photos which have
	 * been owned by the user have been deleted), the album is not deleted.
	 *
	 * Note, the parameter `$skipTreeFixing` should not be used by an external
	 * caller (but left equal to its default `false`).
	 * This flag is only internally used by this method for better performance
	 * and skips rebuilding the tree after each recursion step.
	 *
	 * @param bool $skipTreeFixing
	 *
	 * @return bool
	 */
	public function delete(bool $skipTreeFixing = false): bool
	{
		$success = true;

		$photos = $this->photos()
			->where('owner_id', '=', AccessControl::id())
			->get();
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			// This also takes care of proper deletion of physical files from disk
			$success &= $photo->delete();
		}

		$albums = $this->children()
			->whereHas(
				'base_class',
				fn (Builder $q) => $q->where('owner_id', '=', AccessControl::id())
			)
			->get();
		/** @var Album $album */
		foreach ($albums as $album) {
			$success &= $album->delete(true);
		}

		// Ensure that no invisible child photo nor child album have remained
		$success &= $this->isEmpty();

		// Only forward the call (i.e. actually delete this album,
		// if everything so far has been a success
		if ($success) {
			$success &= $this->forwardDelete();
		}

		/** @var NSQueryBuilder $builder */
		$builder = Album::query();
		if (!$skipTreeFixing && $builder->isBroken()) {
			$builder->fixTree();
		}

		return $success;
	}

	/**
	 * Update the tree after the node has been removed physically.
	 *
	 * This method is copied from
	 * {@link \Kalnoy\Nestedset\NodeTrait::deleteDescendants()}.
	 *
	 * The trait {@link \Kalnoy\Nestedset\NodeTrait} installs a listener for
	 * the event`deleted` which calls this method _after_ the node has been
	 * deleted in order to delete the descendants.
	 *
	 * However, in our case the descendants are tried to be deleted _before_
	 * the parent node is deleted to ensure that the user has sufficient
	 * rights to delete the child nodes and to prevent that non-deletable
	 * child nodes end up without a parent.
	 * See {@link \App\Models\Album::delete()}.
	 *
	 * Hence, the default implementation
	 * {@link \Kalnoy\Nestedset\NodeTrait::deleteDescendants()} should be
	 * harmless.
	 * As the descendants have already been deleted when the `deleted` event
	 * is fired, the implementation should not find any remaining descendants
	 * and thus the whole method should be a no-op.
	 * But for some unintelligible reason the default implementation crashes.
	 * More precisely, the line `$this->descendants()->{$method}();` tries
	 * to build a query for albums and
	 * {@link \Kalnoy\Nestedset\BaseRelation::__construct()}
	 * throws an {@link \InvalidArgumentException} exception which
	 * claims that {@link \App\Models\Album} was not a node.
	 * Obviously, this is bogus ({@link \App\Models\Album} **is** a node);
	 * in particular the same statement is executed many times without any
	 * complains.
	 * As a cheap work-around we simply delete the offending line, because
	 * we know that there are not descendants left which could be deleted.
	 */
	protected function deleteDescendants()
	{
		$lft = $this->getLft();
		$rgt = $this->getRgt();
		$height = $rgt - $lft + 1;
		$this->newNestedSetQuery()->makeGap($rgt + 1, -$height);
		// In case if user wants to re-create the node
		$this->makeRoot();
		static::$actionsPerformed++;
	}
}
