<?php

namespace App\Models;

use App\Contracts\BaseModelAlbum;
use App\Models\Extensions\ForwardsToParentImplementation;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\Thumb;
use App\Relations\HasManyPhotosByTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * Class TagAlbum.
 *
 * @property string show_tags
 */
class TagAlbum extends Model implements BaseModelAlbum
{
	use HasBidirectionalRelationships;
	use ForwardsToParentImplementation;

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
		'show_tags' => null,
	];

	/**
	 * @var string[] The list of attributes which exist as columns of the DB
	 *               relation but shall not be serialized to JSON
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
		//
		// As the sorting logic also expects tag albums to provide the columns
		// max_taken_at and min_taken_at, we add it here, but the columns
		// always have null values.
		// We do not support these columns, because we cannot properly query
		// for those columns on the database level.
		// If we wanted to do so, we had to query for the photos which are
		// included in the album in a similar way as in
		// `HasManyPhotosByTag::addEagerConstraints()`.
		// However, this method utilizes the PHP methods `explode` and `trim`
		// to assemble an SQL query based on the value of the column
		// `show_tags`.
		// If we wanted to write a single SQL query, we would need to perform
		// this string manipulation on the DB layer.
		// This is impossible with standard SQL grammar.
		// In order to solve this problem, we would need a proper table `tags`.
		// Both tables `tag_albums` and `photos` would have a (m:n)-relation
		// with `tags`.
		// Then we could use a proper JOIN clause here.
		// But this is another pull request.
		// TODO: Fix it.
		static::addGlobalScope('add_minmax_taken_at', function (Builder $builder) {
			$builder->addSelect([
				$builder->getQuery()->from . '.*',
				DB::raw('null as max_taken_at'),
				DB::raw('null as min_taken_at'),
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

	public function photos(): HasManyPhotosByTag
	{
		return new HasManyPhotosByTag($this);
	}

	protected function getThumbAttribute(): ?Thumb
	{
		// Note, `photos()` already applies a "security filter" and
		// only returns photos which are accessible by the current
		// user
		return Thumb::createFromPhotoRelation(
			$this->photos(), $this->sorting_col, $this->sorting_order
		);
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

	public function toArray(): array
	{
		$result = parent::toArray();
		$result['tag_album'] = true;

		return array_merge($result, $this->base_class->toArray());
	}
}
