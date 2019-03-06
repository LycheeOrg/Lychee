<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class Album extends Model
{

	protected $dates = [
		'created_at',
		'updated_at',
		'min_takestamp',
		'max_takestamp'
	];



	public function photos()
	{
		return $this->hasMany('App\Photo', 'album_id', 'id');
	}



	/**
	 * Rurns album-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 * @return array Returns album-attributes in a normalized structure.
	 */
	public function prepareData()
	{

		// This function requires the following album-attributes and turns them
		// into a front-end friendly format: id, title, public, sysstamp, password
		// Note that some attributes remain unchanged

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
//        strftime('%B %Y', $this->min_takestamp);
		$album['max_takestamp'] = $this->max_takestamp == null ? '' : $this->max_takestamp->format('M Y');
		//strftime('%B %Y', $this->max_takestamp);

		// Parse password
		$album['password'] = ($this->password == '' ? '0' : '1');

//        dd($this);
		$album['license'] = $this->license == 'none' ? Configs::get_value('default_license') : $this->license;
		// Parse thumbs or set default value
//        $album['thumbs'] = explode(',', $this->thumbs);
//        $album['types'] = (isset($this->types) ? explode(',', $this->types) : array());

		$album['owner'] = $this->owner->username;

		return $album;
	}



	public function gen_thumbs($return)
	{

		$return['thumbs'] = array();
		$return['thumbs2x'] = array();
		$return['types'] = array();

		$alb = $this->get_all_subalbums();
		$alb[] = $this->id;

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
	 *
	 * @param  int   $userId
	 * @return array
	 */
	public function get_albums(int $userId = null) : array
	{
		$subAlbums = [];
		foreach ($this->children as $subAlbum) {

			if (($subAlbum->public == '1' && $subAlbum->visible_hidden == '1') || $userId === 0 || ($userId === $subAlbum->owner->id)) {

				$album = $subAlbum->prepareData();
				$album['albums'] = $subAlbum->get_albums($userId);
				$album = $subAlbum->gen_thumbs($album);

				$subAlbums[] = $album;
			}
		}

		return $subAlbums;
	}



	public function get_all_subalbums($return = array())
	{
		foreach ($this->children as $album) {
			$return[] = $album->id;
			$album->get_all_subalbums($return);
		}
		return $return;
	}



	/**
	 * @param string $password
	 * @return boolean Returns when album is public.
	 */
	public function checkPassword(string $password)
	{

		// Check if password is correct
		return ($this->password == '' || Hash::check($password, $this->password));
//        if ($this->password == '') return true;
//        if ($this->password === crypt($password, $this->password)) return true;
//        return false;

	}



	public function update_min_max_takestamp()
	{
		$album_list = $this->get_all_subalbums([$this->id]);
		$min = Photo::whereIn('album_id', $album_list)->min('takestamp');
		$max = Photo::whereIn('album_id', $album_list)->max('takestamp');
		$this->min_takestamp = $min;
		$this->max_takestamp = $max;
	}



	static public function reset_takestamp()
	{
		$albums = Album::all();
		foreach ($albums as $album) {
			$album->update_min_max_takestamp();
			$album->save();
		}
	}



	public function owner()
	{
		return $this->belongsTo('App\User', 'owner_id', 'id')->withDefault([
			'id' => 0,
			'username' => 'Admin'
		]);
	}



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
					$query->whereIn('id', function ($query) use ($id) {
						$query->select('album_id')
							->from('user_album')
							->where('user_id', '=', $id);
					})
						// or album is visible to user
						->orWhere(
							function ($query) {
								$query->where('public', '=', true)->where('visible_hidden', '=', true);
							});
				})
			->orderBy('owner_id', 'ASC')
			->orderBy(Configs::get_value('sortingAlbums_col'), Configs::get_value('sortingAlbums_order'))
			->get();
	}



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



	public function children()
	{
		return $this->hasMany('App\Album', 'parent_id', 'id');
	}



	public function parent()
	{
		return $this->belongsTo('App\Album', 'id', 'parent_id');
	}



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
