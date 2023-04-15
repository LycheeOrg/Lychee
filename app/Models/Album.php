<?php

namespace App\Models;

use App\Actions\Album\Delete;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Models\Extensions\AlbumBuilder;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Relations\HasAlbumThumb;
use App\Relations\HasManyChildAlbums;
use App\Relations\HasManyChildPhotos;
use App\Relations\HasManyPhotosRecursively;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Kalnoy\Nestedset\DescendantsRelation;
use Kalnoy\Nestedset\Node;
use Kalnoy\Nestedset\NodeTrait;

/**
 * Class Album.
 *
 * @property string                    $id
 * @property string|null               $parent_id
 * @property Album|null                $parent
 * @property Collection<Album>         $children
 * @property int                       $num_children     The number of children.
 * @property Collection<Photo>         $all_photos
 * @property int                       $num_photos       The number of photos in this album (excluding photos in subalbums).
 * @property string                    $license
 * @property string|null               $cover_id
 * @property Photo|null                $cover
 * @property string|null               $track_short_path
 * @property string|null               $track_url
 * @property int                       $_lft
 * @property int                       $_rgt
 * @property \App\Models\BaseAlbumImpl $base_class
 * @property \App\Models\User|null     $owner
 *
 * @method static AlbumBuilder query()                       Begin querying the model.
 * @method static AlbumBuilder with(array|string $relations) Begin querying the model with eager loading.
 * @method        AlbumBuilder newModelQuery()               Get a new, "pure" query builder for the model's table without any scopes, eager loading, etc.
 * @method        AlbumBuilder newQuery()                    Get a new query builder for the model's table.
 */
class Album extends BaseAlbum implements Node
{
	use NodeTrait;
	use ToArrayThrowsNotImplemented;

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
		'parent_id' => null,
		'license' => 'none',
		'cover_id' => null,
		'_lft' => null,
		'_rgt' => null,
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'min_taken_at' => 'datetime',
		'max_taken_at' => 'datetime',
		'num_children' => 'integer',
		'num_photos' => 'integer',
		'_lft' => 'integer',
		'_rgt' => 'integer',
	];

	/**
	 * The relationships that should always be eagerly loaded by default.
	 */
	protected $with = ['cover', 'cover.size_variants', 'thumb'];

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
			return Configs::getValueAsString('default_license');
		}

		return $value;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
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
			->whereExists(function (BaseBuilder $q) use ($lft, $rgt) {
				$q
					->from('albums')
					->whereColumn('base_albums.id', '=', 'albums.id')
					->whereBetween('albums._lft', [$lft + 1, $rgt - 1]);
			})
			->update(['owner_id' => $this->owner_id]);
		Photo::query()
			->whereExists(function (BaseBuilder $q) use ($lft, $rgt) {
				$q
					->from('albums')
					->whereColumn('photos.album_id', '=', 'albums.id')
					->whereBetween('albums._lft', [$lft, $rgt]);
			})
			->update(['owner_id' => $this->owner_id]);
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

	/**
	 * Accessor for the "virtual" attribute {@link Album::$track_url}.
	 *
	 * This is a convenient method which wraps
	 * {@link Album::$track_short_path} into
	 * {@link \Illuminate\Support\Facades\Storage::url()}.
	 *
	 * @return string|null the url of the track
	 */
	public function getTrackUrlAttribute(): ?string
	{
		return $this->track_short_path !== null && $this->track_short_path !== '' ?
			Storage::url($this->track_short_path) : null;
	}

	/**
	 * Set the GPX track for the album.
	 *
	 * @param UploadedFile $file the GPX track file to be set
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
	 */
	public function setTrack(UploadedFile $file): void
	{
		try {
			if ($this->track_short_path !== null) {
				Storage::delete($this->track_short_path);
			}

			$new_track_id = strtr(base64_encode(random_bytes(18)), '+/', '-_');
			Storage::putFileAs('tracks/', $file, "$new_track_id.xml");
			$this->track_short_path = "tracks/$new_track_id.xml";
			$this->save();
		} catch (ModelDBException $e) {
			throw $e;
		} catch (\Exception $e) {
			throw new MediaFileOperationException('Could not save track file', $e);
		}
	}

	/**
	 * Delete the track of the album.
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function deleteTrack(): void
	{
		if ($this->track_short_path === null) {
			return;
		}
		Storage::delete($this->track_short_path);
		$this->track_short_path = null;
		$this->save();
	}
}
