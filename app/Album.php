<?php
/** @noinspection PhpUndefinedClassInspection */

namespace App;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 * App\Album
 *
 * @property int $id
 * @property string $title
 * @property int $owner_id
 * @property int|null $parent_id
 * @property string $description
 * @property Carbon|null $min_takestamp
 * @property Carbon|null $max_takestamp
 * @property int $public
 * @property int $visible_hidden
 * @property int $downloadable
 * @property string|null $password
 * @property string $license
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Album[] $children
 * @property-read User $owner
 * @property-read Album $parent
 * @property-read Photo[] $photos
 * @method static Builder|Album newModelQuery()
 * @method static Builder|Album newQuery()
 * @method static Builder|Album query()
 * @method static Builder|Album whereCreatedAt($value)
 * @method static Builder|Album whereDescription($value)
 * @method static Builder|Album whereDownloadable($value)
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
 * @mixin Eloquent
 */
class Album extends Model
{

	protected $dates = [
		'created_at',
		'updated_at',
		'min_takestamp',
		'max_takestamp'
	];



	/**
	 * Return the relationship between Photos and their Album
	 *
	 * @return HasMany
	 */
	public function photos()
	{
		return $this->hasMany('App\Photo', 'album_id', 'id');
	}



	/**
	 * Return the relationship between an album and its owner
	 *
	 * @return BelongsTo
	 */
	public function owner()
	{
		return $this->belongsTo('App\User', 'owner_id', 'id')->withDefault([
			'id'       => 0,
			'username' => 'Admin'
		]);
	}



	/**
	 * Return the relationship between an album and its sub albums
	 *
	 * @return HasMany
	 */
	public function children()
	{
		return $this->hasMany('App\Album', 'parent_id', 'id');
	}



	/**
	 * Return the relationship between a sub album and its parent
	 *
	 * @return BelongsTo
	 */
	public function parent()
	{
		return $this->belongsTo('App\Album', 'id', 'parent_id');
	}



	/**
	 * Returns album-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array
	 */
	public function prepareData()
	{

		// Init
		$album = array();

		// Set unchanged attributes
		$album['id'] = $this->id;
		$album['title'] = $this->title;
		$album['public'] = strval($this->public);
		$album['hidden'] = strval($this->visible_hidden);
		$album['parent_id'] = $this->parent_id;

		// Additional attributes
		// Only part of $album when available
		$album['description'] = strval($this->description);
		$album['visible'] = strval($this->visible_hidden);
		$album['downloadable'] = strval($this->downloadable);

		// Parse date
		$album['sysdate'] = $this->created_at->format('F Y');
		$album['min_takestamp'] = $this->min_takestamp == null ? '' : $this->min_takestamp->format('M Y');
		$album['max_takestamp'] = $this->max_takestamp == null ? '' : $this->max_takestamp->format('M Y');

		// Parse password
		$album['password'] = ($this->password == '' ? '0' : '1');

		$album['license'] = $this->license == 'none' ? Configs::get_value('default_license') : $this->license;

		$album['owner'] = $this->owner->username;

		$album['thumbs'] = array();
		$album['thumbs2x'] = array();
		$album['types'] = array();

		return $album;
	}



	/**
	 * get the thumbs of an album.
	 * TODO: Check if this may leak private pictures
	 *
	 * @param array $return
	 * @return array
	 */
	public function gen_thumbs($return)
	{

		// First we get the list of all sub albums
		$alb = $this->get_all_sub_albums();
		$alb[] = $this->id;

		/** @noinspection PhpUndefinedMethodInspection (select) */
		$thumbs_types = Photo::select('thumbUrl', 'thumb2x', 'type')
			->whereIn('album_id', $alb)
			->orderBy('star', 'DESC')
			->orderBy(Configs::get_value('sortingPhotos_col'), Configs::get_value('sortingPhotos_order'))
			->limit(3)->get();

		// For each thumb
		$k = 0;
		foreach ($thumbs_types as $thumb_types) {
			$return['thumbs'][$k] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB').$thumb_types->thumbUrl;
			if ($thumb_types->thumb2x == '1') {
				$thumbUrl2x = explode(".", $thumb_types->thumbUrl);
				$thumbUrl2x = $thumbUrl2x[0].'@2x.'.$thumbUrl2x[1];
				$return['thumbs2x'][$k] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB').$thumbUrl2x;
			}
			else {
				$return['thumbs2x'][$k] = '';
			}
			$return['types'][$k] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB').$thumb_types->type;
			$k++;
		}

		return $return;
	}



	/**
	 * Recursively returns the tree structure of albums. Private user albums are returned
	 * only if `$userId` is set.
	 * TODO: Remove $userId dependency.
	 *
	 * @param int $userId
	 * @return array
	 */
	public function get_albums(int $userId = null): array
	{
		$subAlbums = [];
		foreach ($this->children as $subAlbum) {

			if (($subAlbum->public == '1' && $subAlbum->visible_hidden == '1') || $userId === 0 || ($userId === $subAlbum->owner->id)) {

				$album = $subAlbum->prepareData();
				$album['albums'] = $subAlbum->get_albums($userId);
				if ($subAlbum->password === null || Session::get('login')) {
					$album = $subAlbum->gen_thumbs($album);
				}

				$subAlbums[] = $album;
			}
		}

		return $subAlbums;
	}



	/**
	 * Recursively go through each sub album and build a list of them.
	 * TODO: prevent private user albums to be returned if $userId is not set.
	 *
	 * @param array $return
	 * @return array
	 */
	public function get_all_sub_albums($return = array())
	{
		foreach ($this->children as $album) {
			$return[] = $album->id;
			$album->get_all_sub_albums($return);
		}
		return $return;
	}



	/**
	 * Given a password, check if it matches albums password
	 *
	 * @param string $password
	 * @return boolean Returns when album is public.
	 */
	public function checkPassword(string $password)
	{

		// album password is empty or input is correct.
		return ($this->password == '' || Hash::check($password, $this->password));
	}



	/**
	 * Go through each sub album and update the minimum and maximum takestamp of the pictures.
	 */
	public function update_min_max_takestamp()
	{
		$album_list = $this->get_all_sub_albums([$this->id]);

		/** @noinspection PhpUndefinedMethodInspection (WhereIn) */
		$min = Photo::whereIn('album_id', $album_list)->min('takestamp');
		/** @noinspection PhpUndefinedMethodInspection (WhereIn) */
		$max = Photo::whereIn('album_id', $album_list)->max('takestamp');
		$this->min_takestamp = $min;
		$this->max_takestamp = $max;
	}



	/**
	 * Apply the previous method on each album in the database
	 */
	static public function reset_takestamp()
	{
		$albums = Album::all();
		foreach ($albums as $album) {
			$album->update_min_max_takestamp();
			$album->save();
		}
	}



	/**
	 * Given a user, retrieve all the shared albums it can see.
	 * TODO: Move this function to another file
	 *
	 * @param $id
	 * @return Album[]
	 */
	public static function get_albums_user($id)
	{
		return Album::with([
			'owner',
			'children'
		])
			->where('owner_id', '<>', $id)
			->where('parent_id', '=', null)
			->Where(
				function ($query) use ($id) {
					// album is shared with user
					/** @noinspection PhpUndefinedMethodInspection (whereIn) */
					$query->whereIn('id', function ($query) use ($id) {
						/** @noinspection PhpUndefinedMethodInspection (select) */
						$query->select('album_id')
							->from('user_album')
							->where('user_id', '=', $id);
					})
						// or album is visible to user
						->orWhere(
							function ($query) {
								/** @noinspection PhpUndefinedMethodInspection (where) */
								$query->where('public', '=', true)->where('visible_hidden', '=', true);
							});
				})
			->orderBy('owner_id', 'ASC')
			->orderBy(Configs::get_value('sortingAlbums_col'), Configs::get_value('sortingAlbums_order'))
			->get();
	}



	/**
	 * Given two list of albums, merge them without duplicates.
	 * Current complexity is in O(n^2)
	 * TODO: Move this function to another file
	 *
	 * @param Album[] $albums1
	 * @param Album[] $albums2
	 * @return array
	 */
	public static function merge(array $albums1, array $albums2)
	{
		$return = $albums1;

		foreach ($albums2 as $album2_t) {
			$found = false;
			foreach ($albums1 as $album1_t) {
				if ($album1_t->id == $album2_t->id) {
					$found = true;
					break;
				}
			}

			if (!$found) {
				$return[] = $album2_t;
			}
		}

		return $return;
	}




	/**
	 * Before calling delete() to remove the album from the database
	 * we need to go through each sub album and delete it.
	 * Idem we also delete each pictures inside an album (recursively).
	 *
	 * @return bool|null
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
}
