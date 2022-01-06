<?php

namespace App\Models;

use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Models\Extensions\AlbumBuilder;
use App\Models\Extensions\BaseAlbum;
use App\Relations\HasAlbumThumb;
use App\Relations\HasManyChildAlbums;
use App\Relations\HasManyChildPhotos;
use App\Relations\HasManyPhotosRecursively;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Kalnoy\Nestedset\DescendantsRelation;
use Kalnoy\Nestedset\Node;
use Kalnoy\Nestedset\NodeTrait;

/**
 * Class Album.
 *
 * @property string|null       $parent_id
 * @property Album|null        $parent
 * @property Collection<Album> $children
 * @property Collection<Photo> $all_photos
 * @property string            $license
 * @property string|null       $cover_id
 * @property Photo|null        $cover
 * @property int               $_lft
 * @property int               $_rgt
 *
 * @method static       AlbumBuilder query()                       Begin querying the model.
 * @method static       AlbumBuilder with(array|string $relations) Begin querying the model with eager loading.
 * @method AlbumBuilder newModelQuery()                            Get a new, "pure" query builder for the model's table without any scopes, eager loading, etc.
 * @method AlbumBuilder newQuery()                                 Get a new query builder for the model's table.
 */
class Album extends BaseAlbum implements Node
{
	use NodeTrait;

	public const FRIENDLY_MODEL_NAME = 'album';

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
		'_lft' => 'integer',
		'_rgt' => 'integer',
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
	 * The relationships that should always be eagerly loaded by default.
	 */
	protected $with = ['cover', 'thumb'];

	/**
	 * Return the relationship between this album and photos which are
	 * direct children of this album.
	 *
	 * @return HasManyChildPhotos
	 */
	public function photos(): HasManyChildPhotos
	{
		return new HasManyChildPhotos($this);
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

	public function thumb(): HasAlbumThumb
	{
		return new HasAlbumThumb($this);
	}

	/**
	 * Return the relationship between an album and its sub-albums.
	 *
	 * @return HasManyChildAlbums
	 */
	public function children(): HasManyChildAlbums
	{
		return new HasManyChildAlbums($this);
	}

	/**
	 * Get query for descendants of the node.
	 *
	 * @return DescendantsRelation
	 *
	 * @throws QueryBuilderException
	 */
	public function descendants(): DescendantsRelation
	{
		try {
			return new DescendantsRelation($this->newQuery(), $this);
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
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

		// The client expect the relation "children" to be named "albums".
		// Rename it
		if (key_exists('children', $result)) {
			$result['albums'] = $result['children'];
			unset($result['children']);
		}

		return $result;
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
	 * @return bool always returns true
	 *
	 * @throws ModelDBException
	 */
	public function delete(bool $skipTreeFixing = false): bool
	{
		try {
			$this->refreshNode();

			// Delete all recursive child photos first
			$photos = $this->all_photos()->lazy();
			/** @var Photo $photo */
			foreach ($photos as $photo) {
				// This also takes care of proper deletion of physical files from disk
				$photo->delete();
			}

			// Finally, delete the album itself
			// Note, we need this strange condition, because `delete` may also
			// return `null` on success, so we must explicitly test for
			// _not `false`_.
			parent::delete();

			return true;
		} catch (ModelDBException $e) {
			try {
				// if anything goes wrong, don't leave the tree in an inconsistent state
				$this->newModelQuery()->fixTree();
			} catch (\Throwable) {
				// Sic! We cannot do anything about the inner exception
			}
			throw $e;
		}
	}

	/**
	 * Create a new Eloquent query builder for the model.
	 *
	 * @param BaseBuilder $query
	 *
	 * @return AlbumBuilder
	 */
	public function newEloquentBuilder($query): AlbumBuilder
	{
		return new AlbumBuilder($query);
	}

	protected function friendlyModelName(): string
	{
		return self::FRIENDLY_MODEL_NAME;
	}
}
