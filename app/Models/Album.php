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
 * @property int|null   $parent_id
 * @property Album|null $parent
 * @property Collection $children
 * @property Collection $all_photos
 * @property string     $license
 * @property int|null   $cover_id
 * @property Photo|null $cover
 * @property int        $_lft
 * @property int        $_rgt
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
		'id' => 'integer',
		'min_taken_at' => 'datetime',
		'max_taken_at' => 'datetime',
		'cover_id' => 'integer',
		'parent_id' => 'integer',
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
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
	{
		static::deleting(function (Album $album) {
			$album->refreshNode();
			$result = $album->deleteAllPhotosRecursively();
			$album->deleteDescendants();

			return $result;
		});
	}

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

	public function deleteAllPhotosRecursively(): bool
	{
		$success = true;
		$photos = $this->all_photos()->lazy();
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			// This also takes care of proper deletion of physical files from disk
			$success &= $photo->delete();
		}

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
