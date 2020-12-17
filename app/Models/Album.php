<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Album.
 *
 * @property int               $id
 * @property string            $title
 * @property int               $owner_id
 * @property int|null          $parent_id
 * @property string            $description
 * @property Carbon|null       $min_takestamp
 * @property Carbon|null       $max_takestamp
 * @property int               $public
 * @property int               $full_photo
 * @property int               $viewable
 * @property int               $downloadable
 * @property int               $share_button_visible
 * @property string|null       $password
 * @property string            $license
 * @property bool              $smart
 * @property text              $showtags
 * @property Carbon|null       $created_at
 * @property Carbon|null       $updated_at
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
class Album extends Model
{
	protected $dates
	= [
		'created_at',
		'updated_at',
		'min_takestamp',
		'max_takestamp',
	];

	protected $casts
	= [
		'public' => 'int',
		'nsfw' => 'int',
		'viewable' => 'int',
		'downloadable' => 'int',
		'share_button_visible' => 'int',
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
	 * Return the list of photos.
	 */
	public function get_photos()
	{
		return $this->photos();
	}

	/**
	 * Return the relationship between an album and its owner.
	 *
	 * @return BelongsTo
	 */
	public function owner()
	{
		return $this->belongsTo('App\Models\User', 'owner_id', 'id')->withDefault([
			'id' => 0,
			'username' => 'Admin',
		]);
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
	 * Return whether or not public users will see the full photo.
	 *
	 * @return bool
	 */
	public function is_full_photo_visible()
	{
		if ($this->public) {
			return $this->full_photo == 1;
		} else {
			return Configs::get_value('full_photo', '1') === '1';
		}
	}

	/**
	 * Return parent_id as a string or null.
	 *
	 * @return string|null
	 */
	public function str_parent_id()
	{
		return $this->parent_id == null ? '' : strval($this->parent_id);
	}

	/**
	 * Return min_takestamp as a string or ''.
	 *
	 * @return string
	 */
	public function str_min_takestamp()
	{
		return $this->min_takestamp == null ? '' : $this->min_takestamp->format('M Y');
	}

	/**
	 * Return min_takestamp as a string or ''.
	 *
	 * @return string
	 */
	public function str_max_takestamp()
	{
		return $this->max_takestamp == null ? '' : $this->max_takestamp->format('M Y');
	}

	/**
	 * Return whether or not public users can download photos.
	 *
	 * @return bool
	 */
	public function is_downloadable()
	{
		if ($this->public) {
			return $this->downloadable == 1;
		} else {
			return Configs::get_value('downloadable', '0') === '1';
		}
	}

	/**
	 * Return whether or not display share button.
	 *
	 * @return bool
	 */
	public function is_share_button_visible()
	{
		if ($this->public) {
			return $this->share_button_visible == 1;
		} else {
			return Configs::get_value('share_button_visible', '0') === '1';
		}
	}

	/**
	 * Return the Album license or the default one.
	 *
	 * @return string
	 */
	public function get_license()
	{
		if ($this->license == 'none') {
			return Configs::get_value('default_license');
		}

		return $this->license;
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
		$albums = $this->children;

		foreach ($albums as $album) {
			$no_error &= $album->predelete();
			$no_error &= $album->delete();
		}

		$photos = $this->photos;
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
