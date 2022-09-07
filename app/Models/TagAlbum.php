<?php

namespace App\Models;

use App\Casts\ArrayCast;
use App\Exceptions\InvalidPropertyException;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\TagAlbumBuilder;
use App\Models\Extensions\Thumb;
use App\Relations\HasManyPhotosByTag;
use Illuminate\Database\Query\Builder as BaseBuilder;

/**
 * Class TagAlbum.
 *
 * @property string[] $show_tags
 *
 * @method static TagAlbumBuilder query()                       Begin querying the model.
 * @method static TagAlbumBuilder with(array|string $relations) Begin querying the model with eager loading.
 */
class TagAlbum extends BaseAlbum
{
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
		'show_tags' => null,
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'min_taken_at' => 'datetime',
		'max_taken_at' => 'datetime',
		'show_tags' => ArrayCast::class,
	];

	/**
	 * @var array<int,string> The list of attributes which exist as columns of the DB
	 *                        relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'base_class', // don't serialize base class as a relation, the attributes of the base class are flatly merged into the JSON result
	];

	/**
	 * @var string[] The list of "virtual" attributes which do not exist as
	 *               columns of the DB relation but which shall be appended to
	 *               JSON from accessors
	 */
	protected $appends = [
		'thumb',
	];

	public function photos(): HasManyPhotosByTag
	{
		return new HasManyPhotosByTag($this);
	}

	/**
	 * Returns the value for the virtual attribute {@link TagAlbum::$thumb}.
	 *
	 * Note, opposed to {@link Album} the thumbnail of a tag album cannot be
	 * converted into a proper relation (cp. {@link Album::thumb()}).
	 * However, doing so would enable to eagerly load all thumbs of all
	 * tag albums at once (using a single query) and cache the result.
	 * This would speed up rendering the root album.
	 * The main obstacle is the way how tags of photos and tags of albums
	 * are matched to each other.
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
	 * @return Thumb|null
	 *
	 * @throws InvalidPropertyException
	 */
	protected function getThumbAttribute(): ?Thumb
	{
		// Note, `photos()` already applies a "security filter" and
		// only returns photos which are accessible by the current
		// user
		return Thumb::createFromQueryable(
			$this->photos(),
			$this->getEffectiveSorting()
		);
	}

	public function toArray(): array
	{
		$result = parent::toArray();
		$result['is_tag_album'] = true;

		return $result;
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
}
