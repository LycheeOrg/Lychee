<?php

namespace App\Models;

use App\Assets\HasManyBidirectionally;
use App\Contracts\BaseModelAlbum;
use App\Models\Extensions\ForwardsToParentImplementation;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\NodeTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Class Album.
 *
 * @property int|null   $parent_id
 * @property Album|null $parent
 * @property Collection $children
 * @property string     $license
 * @property int|null   $cover_id
 * @property int        $_lft
 * @property int        $_rgt
 */
class Album extends Model implements BaseModelAlbum
{
	use NodeTrait;
	use HasBidirectionalRelationships;
	use ForwardsToParentImplementation;

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
			BaseAlbumImpl::class,
			BaseAlbumImpl::INHERITANCE_RELATION_NAME,
			BaseAlbumImpl::INHERITANCE_DISCRIMINATOR_COL_NAME,
			BaseAlbumImpl::INHERITANCE_ID_COL_NAME,
			BaseAlbumImpl::INHERITANCE_ID_COL_NAME
		);
	}

	/**
	 * Return the relationship between Photos and their Album.
	 *
	 * @return HasManyBidirectionally
	 */
	public function photos(): HasManyBidirectionally
	{
		return $this->hasManyBidirectionally(Photo::class);
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
	 * @return HasMany
	 */
	public function children(): HasMany
	{
		return $this->hasMany('App\Models\Album', 'parent_id', 'id');
	}

	/**
	 * Return the relationship between an album and its cover.
	 *
	 * @return HasOne
	 */
	public function cover(): HasOne
	{
		return $this->hasOne('App\Models\Photo', 'id', 'cover_id');
	}

	/**
	 * Return the relationship between an album and its parent.
	 *
	 * @return BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo('App\Models\Album', 'parent_id', 'id');
	}
}