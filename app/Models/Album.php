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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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

	protected $casts = [
		'min_taken_at' => 'datetime',
		'max_taken_at' => 'datetime',
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
	protected $appends = [
		'thumb',
		'min_taken_at',
		'max_taken_at',
	];

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
	 * @return MorphOne
	 */
	public function base_class(): MorphOne
	{
		return $this->morphOne(
			BaseModelAlbumImpl::class,
			BaseModelAlbumImpl::INHERITANCE_RELATION_NAME,
			BaseModelAlbumImpl::INHERITANCE_DISCRIMINATOR_COL_NAME,
			BaseModelAlbumImpl::INHERITANCE_ID_COL_NAME,
			BaseModelAlbumImpl::INHERITANCE_ID_COL_NAME
		);
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
		// Note, `all_photos` already applies a "security filter" and
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

	protected function getLicenseAttribute(string $value): string
	{
		if ($value === 'none') {
			return Configs::get_value('default_license');
		}

		return $value;
	}

	public function toArray(): array
	{
		$result = parent::toArray();
		$result['has_albums'] = !$this->isLeaf();

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
			->where('owner_id', '=', AccessControl::id())
			->get();
		/** @var Album $album */
		foreach ($albums as $album) {
			$success &= $album->delete(true);
		}

		// Ensure that no child photo nor child album has remained
		$success &= $this->photos()->count() === 0 && $this->children()->count() === 0;

		// Only forward the call (i.e. actually delete this album, if everything so far has been a success
		if ($success) {
			$success &= $this->forwardDelete();
		}

		/** @var \Kalnoy\Nestedset\QueryBuilder $builder */
		$builder = Album::query();
		if (!$skipTreeFixing && $builder->isBroken()) {
			$builder->fixTree();
		}

		return $success;
	}
}
