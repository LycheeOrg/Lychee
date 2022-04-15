<?php

namespace App\Models;

use App\Actions\Album\Delete;
use App\Models\Extensions\AlbumBuilder;
use App\Models\Extensions\BaseAlbum;
use App\Relations\HasAlbumThumb;
use App\Relations\HasManyChildAlbums;
use App\Relations\HasManyChildPhotos;
use App\Relations\HasManyPhotosRecursively;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
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
 * @method static       AlbumBuilder query()
 * @method AlbumBuilder newModelQuery()
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

	/**
	 * {@inheritDoc}
	 */
	public function performDeleteOnModel(): void
	{
		$fileDeleter = (new Delete())->do([$this->id]);
		$this->exists = false;
		$fileDeleter->do();
	}

	/**
	 * This method is a no-op.
	 *
	 * This method is originally defined by {@link NodeTrait::deleteDescendants()}
	 * and called as part of the event listener for the 'deleting' event.
	 * The event listener is installed by {@link NodeTrait::bootNodeTrait()}.
	 *
	 * For efficiency reasons all descendants are deleted by
	 * {@link Delete::do()}.
	 * Hence, we must avoid any attempt to delete the descendants twice.
	 *
	 * @return void
	 */
	protected function deleteDescendants(): void
	{
		// deliberately a no op
	}

	/**
	 * Sets the ownership of all child albums and child photos to the owner
	 * of this album.
	 *
	 * ANSI SQL does not allow a `JOIN`-clause in the table reference
	 * of `UPDATE` statements.
	 * MySQL and PostgreSQL have their proprietary but different
	 * extension for that, SQLite does not support it at all.
	 * Hence, we must use a (slightly) less efficient, but
	 * SQL-compatible `WHERE EXIST` condition instead of a `JOIN`.
	 * This also means that we cannot use the succinct statements
	 *
	 *     $this->descendants()->update(['owner_id' => $this->owner_id])
	 *     $this->all_photos()->update(['owner_id' => $this->owner_id])
	 *
	 * because these method return queries which use `JOINS`.
	 * So, we need to build the queries from scratch.
	 *
	 * @return void
	 */
	public function fixOwnershipOfChildren(): void
	{
		$this->refreshNode();
		$lft = $this->_lft;
		$rgt = $this->_rgt;

		BaseAlbumImpl::query()
			->whereExists(function (Builder $q) use ($lft, $rgt) {
				$q
					->from('albums')
					->whereColumn('base_albums.id', '=', 'albums.id')
					->whereBetween('albums._lft', [$lft + 1, $rgt - 1]);
			})
			->update(['owner_id' => $this->owner_id]);
		Photo::query()
			->whereExists(function (Builder $q) use ($lft, $rgt) {
				$q
					->from('albums')
					->whereColumn('photos.album_id', '=', 'albums.id')
					->whereBetween('albums._lft', [$lft, $rgt]);
			})
			->update(['owner_id' => $this->owner_id]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function newEloquentBuilder($query): AlbumBuilder
	{
		return new AlbumBuilder($query);
	}
}
