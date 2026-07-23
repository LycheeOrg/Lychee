<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\DTO\EffectiveAccessPermission;
use App\Exceptions\InvalidPropertyException;
use App\ModelFunctions\HasAbstractAlbumProperties;
use App\Models\Builders\TagAlbumBuilder;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\CachesAlbumUserThumb;
use App\Models\Extensions\Thumb;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Relations\HasManyPhotosByTag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\TagAlbum.
 *
 * @property string|null         $cover_id
 * @property Photo|null          $cover
 * @property Collection<int,Tag> $tags
 * @property bool                $is_and
 * @property AlbumUserThumb|null $userThumbRow
 *
 * @method static TagAlbumBuilder|TagAlbum query()                       Begin querying the model.
 * @method static TagAlbumBuilder|TagAlbum with(array|string $relations) Begin querying the model with eager loading.
 *
 * @property string                            $id
 * @property BaseAlbumImpl                     $base_class
 * @property User                              $owner
 * @property Collection<int, User>             $shared_with
 * @property int|null                          $shared_with_count
 * @property Collection<int, AccessPermission> $access_permissions
 * @property int|null                          $access_permissions_count
 * @property EffectiveAccessPermission|null    $current_user_permissions
 * @property AccessPermission|null             $public_permissions
 * @property Collection<int, User>             $shared_with
 *
 * @method static TagAlbumBuilder|TagAlbum addSelect($column)
 * @method static TagAlbumBuilder|TagAlbum join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static TagAlbumBuilder|TagAlbum joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static TagAlbumBuilder|TagAlbum leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static TagAlbumBuilder|TagAlbum newModelQuery()
 * @method static TagAlbumBuilder|TagAlbum newQuery()
 * @method static TagAlbumBuilder|TagAlbum orderBy($column, $direction = 'asc')
 * @method static TagAlbumBuilder|TagAlbum select($columns = [])
 * @method static TagAlbumBuilder|TagAlbum whereId($value)
 * @method static TagAlbumBuilder|TagAlbum whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static TagAlbumBuilder|TagAlbum whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static TagAlbumBuilder|TagAlbum whereShowTags($value)
 *
 * @mixin \Eloquent
 */
class TagAlbum extends BaseAlbum
{
	use ToArrayThrowsNotImplemented;
	/** @phpstan-use HasFactory<\Database\Factories\TagAlbumFactory> */
	use HasFactory;
	use HasAbstractAlbumProperties;
	use CachesAlbumUserThumb;

	/**
	 * The model's attributes.
	 *
	 * We must list all attributes explicitly here, otherwise the attributes
	 * of a new model will accidentally be set on the parent class.
	 * The trait {@link \App\Models\Extensions\ForwardsToParentImplementation}
	 * only works properly, if it knows which attributes belong to the parent
	 * class and which attributes belong to the child class.
	 *
	 * @var array<string, mixed>
	 */
	protected $attributes = [
		'id' => null,
		'cover_id' => null,
		'is_and' => null,
	];

	/**
	 * @return array<string,string>
	 */
	protected function casts(): array
	{
		return [
			'is_and' => 'boolean',
			'min_taken_at' => 'datetime',
			'max_taken_at' => 'datetime',
		];
	}

	/**
	 * @var list<string> The list of attributes which exist as columns of the DB
	 *                   relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'base_class', // don't serialize base class as a relation, the attributes of the base class are flatly merged into the JSON result
	];

	/**
	 * @var list<string> The list of "virtual" attributes which do not exist as
	 *                   columns of the DB relation but which shall be appended to
	 *                   JSON from accessors
	 */
	protected $appends = [
		'thumb',
	];

	/**
	 * @return HasOne<Photo,$this>
	 */
	public function cover(): HasOne
	{
		return $this->hasOne(Photo::class, 'id', 'cover_id');
	}

	/**
	 * The current viewer's `album_user_thumbs` cache row for this album, if any.
	 *
	 * Unlike the live thumb computation (see the note on {@link TagAlbum::getThumbAttribute()}),
	 * this cache row has a uniform shape across every tag album, so it can be
	 * eager-loaded (`->with('userThumbRow.photo.size_variants')`) to resolve
	 * an entire list of tag albums' thumbs in a single query instead of one
	 * query per album.
	 *
	 * @return HasOne<AlbumUserThumb,$this>
	 */
	public function userThumbRow(): HasOne
	{
		$query = $this->hasOne(AlbumUserThumb::class, 'album_id', 'id');

		return Auth::check() ? $query->where('user_id', '=', Auth::id()) : $query->whereNull('user_id');
	}

	/**
	 * @phpstan-ignore method.childReturnType, method.childReturnType
	 */
	public function photos(): HasManyPhotosByTag
	{
		return new HasManyPhotosByTag($this);
	}

	/**
	 * Returns the value for the virtual attribute {@link TagAlbum::$thumb}.
	 *
	 * Note, opposed to {@link Album} the *live* thumbnail computation (i.e.
	 * matching photos to this album's tags) cannot itself be converted into
	 * a proper relation (cp. {@link Album::thumb()}): the main obstacle is
	 * the way tags of photos and tags of albums are matched to each other.
	 * At the moment this requires string operations on the PHP level and
	 * the SQL query for each tag album has an individual number of
	 * `WHERE`-clauses which is specific for the particular
	 * tag album (cp. {@link HasManyPhotosByTag::addEagerConstraints()}).
	 * Hence, it is not possible to construct a single SQL query which fetches
	 * the photos for multiple tag albums.
	 * However, this would be possible if we had a proper `tags` table and
	 * two n:m-relations between photos and tags and tags and albums.
	 * This would allow to create a single `JOIN`-query for all tag albums.
	 *
	 * The *cached* thumb (the `album_user_thumbs` row seeded once the live
	 * computation above has run at least once for this viewer) has no such
	 * obstacle - see {@link TagAlbum::userThumbRow()} - so it is preferred
	 * here whenever it has been eager-loaded, and only falls back to the
	 * per-instance query in {@link CachesAlbumUserThumb::getCachedOrLiveThumb()}
	 * otherwise.
	 *
	 * @return Thumb|null
	 *
	 * @throws InvalidPropertyException
	 */
	protected function getThumbAttribute(): ?Thumb
	{
		if ($this->cover_id !== null) {
			return Thumb::createFromPhoto($this->cover);
		}

		if ($this->relationLoaded('userThumbRow') && $this->userThumbRow !== null) {
			return Thumb::createFromPhoto($this->userThumbRow->photo);
		}

		// Note, `photos()` already applies a "security filter" and
		// only returns photos which are accessible by the current
		// user
		return $this->getCachedOrLiveThumb(
			$this->id,
			fn () => Thumb::createFromQueryable(
				$this->photos(),
				$this->getEffectivePhotoSorting(),
			),
		);
	}

	/**
	 * Create a new Eloquent query builder for the model.
	 *
	 * @param BaseBuilder $query
	 *
	 * @return TagAlbumBuilder
	 */
	public function newEloquentBuilder($query): TagAlbumBuilder
	{
		return new TagAlbumBuilder($query);
	}

	/**
	 * Returns the relationship between a tag and all photos with whom
	 * this tag is attached.
	 *
	 * @return BelongsToMany<Tag,$this>
	 */
	public function tags(): BelongsToMany
	{
		return $this->belongsToMany(
			Tag::class,
			'tag_albums_tags',
			'album_id',
			'tag_id',
		);
	}
}
