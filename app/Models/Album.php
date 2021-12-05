<?php

namespace App\Models;

use App\Models\Extensions\AlbumBuilder;
use App\Models\Extensions\BaseAlbum;
use App\Relations\HasAlbumThumb;
use App\Relations\HasManyChildAlbums;
use App\Relations\HasManyChildPhotos;
use App\Relations\HasManyPhotosRecursively;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 */
class Album extends BaseAlbum implements Node
{
	use NodeTrait;

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

	public function delete(): bool
	{
		$this->refreshNode();

		$success = true;

		// Delete all recursive child photos first
		$photos = $this->all_photos()->lazy();
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			// This also takes care of proper deletion of physical files from disk
			// Note, we need this strange condition, because `delete` may also
			// return `null` on success, so we must explicitly test for
			// _not `false`_.
			$success &= ($photo->delete() !== false);
		}

		if (!$success) {
			return false;
		}

		// Delete all recursive child albums
		// Note, although `parent::delete` also deletes all descendants,
		// we must explicitly delete all descendants first.
		// The implementation of the parent class is buggy.
		// It first tries to delete the parent album and then deletes all
		// child albums.
		// However, this always fail due to foreign key constraints between
		// an albums `parent_id` and the `id` of the parent.
		// Child albums must be deleted in correct order from the leaf to the
		// root.
		$this->deleteDescendants();

		// Finally, delete the album itself
		// Note, we need this strange condition, because `delete` may also
		// return `null` on success, so we must explicitly test for
		// _not `false`_.
		$success &= (parent::delete() !== false);

		return $success;
	}

	/**
	 * {@inheritdoc}
	 */
	public function newEloquentBuilder($query): AlbumBuilder
	{
		return new AlbumBuilder($query);
	}
}
