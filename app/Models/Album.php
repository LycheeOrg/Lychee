<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Models;

use App\Contracts\AlbumInterface;
use App\Models\Extensions\AlbumBooleans;
use App\Models\Extensions\AlbumCast;
use App\Models\Extensions\AlbumGetters;
use App\Models\Extensions\AlbumQuery;
use App\Models\Extensions\AlbumSetters;
use App\Models\Extensions\AlbumStringify;
use App\Models\Extensions\CustomSort;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Kalnoy\Nestedset\NodeTrait;

/**
 * App\Album.
 *
 * @property int               $id
 * @property string            $title
 * @property int               $owner_id
 * @property int|null          $parent_id
 * @property string            $description
 * @property Carbon|null       $min_taken_at
 * @property Carbon|null       $max_taken_at
 * @property int               $public
 * @property int               $full_photo
 * @property int               $viewable
 * @property int               $downloadable
 * @property int               $share_button_visible
 * @property string|null       $password
 * @property string            $license
 * @property bool              $smart
 * @property text              $showtags
 * @property Carbon            $created_at
 * @property Carbon            $updated_at
 * @property Collection[Album] $children
 * @property User              $owner
 * @property Album             $parent
 * @property Collection[Photo] $photos
 *
 * @method static Builder|Album newModelQuery()
 * @method static Builder|Album newQuery()
 * @method static Builder|Album query()
 * @method static Builder|Album whereCreatedAt($value)
 * @method static Builder|Album whereDescription($value)
 * @method static Builder|Album whereDownloadable($value)
 * @method static Builder|Album whereShareButtonVisible($value)
 * @method static Builder|Album whereId($value)
 * @method static Builder|Album whereLicense($value)
 * @method static Builder|Album whereMaxTakestamp($value)
 * @method static Builder|Album whereMinTakestamp($value)
 * @method static Builder|Album whereOwnerId($value)
 * @method static Builder|Album whereParentId($value)
 * @method static Builder|Album wherePassword($value)
 * @method static Builder|Album wherePublic($value)
 * @method static Builder|Album whereTitle($value)
 * @method static Builder|Album whereUpdatedAt($value)
 * @method static Builder|Album whereVisibleHidden($value)
 * @method static Builder|Album whereSmart($value)
 * @mixin Eloquent
 *
 * @property Collection|User[] $shared_with
 */
class Album extends PatchedBaseModel implements AlbumInterface
{
	use NodeTrait;
	use AlbumBooleans;
	use AlbumStringify;
	use AlbumGetters;
	use AlbumCast;
	use AlbumSetters;
	use CustomSort;
	use AlbumQuery;

	protected $casts
	= [
		'public' => 'int',
		'nsfw' => 'int',
		'viewable' => 'int',
		'downloadable' => 'int',
		'share_button_visible' => 'int',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'min_taken_at' => 'datetime',
		'max_taken_at' => 'datetime',
	];

	/**
	 * Return the relationship between Photos and their Album.
	 *
	 * @return HasMany
	 */
	public function photos()
	{
		return $this->hasMany('App\Models\Photo', 'album_id', 'id');
	}

	/**
	 * Return the relationship between an album and its owner.
	 *
	 * @return BelongsTo
	 */
	public function owner()
	{
		return $this->belongsTo('App\Models\User', 'owner_id', 'id');
	}

	/**
	 * Return the relationship between an album and its sub albums.
	 *
	 * @return HasMany
	 */
	public function children()
	{
		return $this->hasMany('App\Models\Album', 'parent_id', 'id');
	}

	/**
	 * Return the relationship between a sub album and its parent.
	 *
	 * @return BelongsTo
	 */
	public function cover()
	{
		return $this->hasOne('App\Models\Photo', 'id', 'cover_id');
	}

	/**
	 * Return the relationship between a cover picture and its parent.
	 *
	 * @return BelongsTo
	 */
	public function parent()
	{
		return $this->belongsTo('App\Models\Album', 'parent_id', 'id');
	}

	/**
	 * @return BelongsToMany
	 */
	public function shared_with()
	{
		return $this->belongsToMany(
			'App\Models\User',
			'user_album',
			'album_id',
			'user_id'
		);
	}

	/**
	 * Before calling delete() to remove the album from the database
	 * we need to go through each sub album and delete it.
	 * Idem we also delete each pictures inside an album (recursively).
	 *
	 * @return bool|null
	 *
	 * @throws Exception
	 */
	public function predelete()
	{
		$no_error = true;
		$photos = $this->get_all_photos()->get();
		foreach ($photos as $photo) {
			$no_error &= $photo->predelete();
			$no_error &= $photo->delete();
		}

		return $no_error;
	}

	/**
	 * Return the full path of the album consisting of all its parents' titles.
	 *
	 * @return string
	 */
	public static function getFullPath($album)
	{
		$title = [$album->title];
		$parentId = $album->parent_id;
		while ($parentId) {
			$parent = Album::find($parentId);
			array_unshift($title, $parent->title);
			$parentId = $parent->parent_id;
		}

		return implode('/', $title);
	}
}
