<?php

namespace App\ModelRessources;

use App\Album;
use App\Assets\Helpers;
use App\ModelFunctions\SymLinkFunctions;
use App\Photo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AlbumRessources
{
	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	public function __construct(SymLinkFunctions $symLinkFunctions)
	{
		$this->symLinkFunctions = $symLinkFunctions;
	}

	public function getThumbs(Album $album, array &$return)
	{
		$photos = $album->get_photos();
		$return['thumbs'] = [];
		$return['thumbs2x'] = [];
		$return['types'] = [];
		$return['num'] = strval($photos->count());

		/*
		 * @var Photo
		 */
		$i = 0;
		foreach ($photos as $photo) {
			if ($i < 3) {
				$sym = $this->symLinkFunctions->find($photo);
				if ($sym !== null) {
					$return['thumbs'][$i] = $sym->get('thumbUrl');
					// default is '' so if thumb2x does not exist we just reply '' which is the behaviour we want
					$return['thumbs2x'][$i] = $sym->get('thumb2x');
				} else {
					$return['thumbs'][$i] = Storage::url('thumb/' . $photo->thumbUrl);
					if ($photo->thumb2x == '1') {
						$thumbUrl2x = explode('.', $photo->thumbUrl);
						$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
						$return['thumbs2x'][$i] = Storage::url('thumb/' . $thumbUrl2x);
					} else {
						$return['thumbs2x'][$i] = '';
					}
				}
				$return['types'][$i] = $photo->type;
				$i++;
			} else {
				break;
			}
		}
	}

	/**
	 * Returns album-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array
	 */
	public function toArray(Album $album)
	{
		$return = [
			'id' => strval($album->id),
			'title' => $album->title,
			'public' => strval($album->public),
			'full_photo' => Helpers::str_of_bool($album->is_full_photo_visible()),
			'visible' => strval($album->visible_hidden),
			'parent_id' => $album->str_parent_id(),
			'description' => strval($album->description),

			'downloadable' => Helpers::str_of_bool($album->is_downloadable()),
			'share_button_visible' => Helpers::str_of_bool($album->is_share_button_visible()),

			// Parse date
			'sysdate' => $album->created_at->format('F Y'),
			'min_takestamp' => $album->str_min_takestamp(),
			'max_takestamp' => $album->str_max_takestamp(),

			// Parse password
			'password' => Helpers::str_of_bool($album->password != ''),
			'license' => $album->get_license(),

			'thumbs' => [],
			'thumbs2x' => [],
			'types' => [],
		];

		// TODO: FIXME
		// $album['owner'] will be set by the caller as needed.

		// For server use only; will be unset before sending the response
		// to the front end.
		$album['thumbIDs'] = [];

		return $return;
	}

	/**
	 * Given a password, check if it matches albums password.
	 *
	 * @param string $password
	 *
	 * @return bool returns when album is public
	 */
	public function checkPassword(Album $album, string $password)
	{
		// album password is empty or input is correct.
		return $album->password == '' || Hash::check($password, $album->password);
	}

	/**
	 * Recursively go through each sub album and build a list of them.
	 *
	 * @param Album      $album
	 * @param Collection $return
	 *
	 * @return Collection
	 */
	private static function get_all_sub_albums_id(Album $album)
	{
		return $album->children->reduce(function ($collect, $_album) {
			return $collect
				->concat([$_album->id])
				->concat(self::get_all_sub_albums_id($_album));
		}, collect());
	}

	/**
	 * Go through each sub album and update the minimum and maximum takestamp of the pictures.
	 * This is expensive and not normally necessary so we only use it
	 * during migration.
	 */
	public function update_min_max_takestamp(Album $album)
	{
		$album_list = self::get_all_sub_albums_id($album, [$album->id]);

		$min = Photo::whereIn('album_id', $album_list->all())->min('takestamp');
		$max = Photo::whereIn('album_id', $album_list->all())->max('takestamp');
		$album->min_takestamp = $min;
		$album->max_takestamp = $max;
	}

	/**
	 * Update album's min_takestamp and max_takestamp based on changes made
	 * to the album content.  If needed, recursively updates parent album(s).
	 *
	 * @param array $takestamps : an array with the takestamps of changed
	 *                          elements; for albums needs to include both min and max takestamps
	 *                          (including null elements in the array is safe)
	 * @param bool  $adding     :     true if adding new content, false if removing
	 *
	 * @return bool: true if successful
	 */
	public static function update_takestamps(Album $album, array $takestamps, bool $adding)
	{
		// Begin by calculating min and max takestamps from the array.
		// The array may contain null values, which is why we can't use the
		// built-in min() function for this (it will always return null if
		// present).  For consistency, we don't use the built-in max()
		// either.
		$minTS = $maxTS = null;
		foreach ($takestamps as $takestamp) {
			if ($takestamp !== null) {
				if ($minTS === null || $minTS > $takestamp) {
					$minTS = $takestamp;
				}
				if ($maxTS === null || $maxTS < $takestamp) {
					$maxTS = $takestamp;
				}
			}
		}
		if ($minTS === null || $maxTS === null) {
			return true;
		}

		$no_error = true;
		$changed = false;

		if ($adding) {
			// Adding is easy: essentially a single operation per takestamp.
			if (
				$album->min_takestamp === null
				|| $album->min_takestamp > $minTS
			) {
				$album->min_takestamp = $minTS;
				$changed = true;
			}
			if (
				$album->max_takestamp === null
				|| $album->max_takestamp < $maxTS
			) {
				$album->max_takestamp = $maxTS;
				$changed = true;
			}
		} else {
			// We're removing.  That can be more complicated, requiring us
			// to rescan the content at the current level to find the new
			// min/max.
			if ($album->min_takestamp == $minTS) {
				$min_photos = Photo::where('album_id', '=', $album->id)
					->whereNotNull('takestamp')->min('takestamp');
				$min_albums = Album::where('parent_id', '=', $album->id)
					->whereNotNull('min_takestamp')->min('min_takestamp');
				if ($min_photos !== null && $min_albums !== null) {
					$album->min_takestamp = min($min_photos, $min_albums);
				} elseif ($min_photos !== null) {
					$album->min_takestamp = $min_photos;
				} else {
					$album->min_takestamp = $min_albums;
				}
				$changed = true;
			}
			if ($album->max_takestamp == $maxTS) {
				$max_photos = Photo::where('album_id', '=', $album->id)
					->whereNotNull('takestamp')->max('takestamp');
				$max_albums = Album::where('parent_id', '=', $album->id)
					->whereNotNull('max_takestamp')->max('max_takestamp');
				if ($max_photos !== null && $max_albums !== null) {
					$album->max_takestamp = max($max_photos, $max_albums);
				} elseif ($max_photos !== null) {
					$album->max_takestamp = $max_photos;
				} else {
					$album->max_takestamp = $max_albums;
				}
				$changed = true;
			}
		}

		if ($changed) {
			$no_error &= $album->save();

			// Since we changed our takestamps, we need to recursively ascend
			// up the album tree to give the parent albums a chance to
			// update their takestamps as well.
			if ($album->parent_id !== null) {
				$no_error &= self::update_takestamps(
					$album->parent,
					[$minTS, $maxTS],
					$adding
				);
			}
		}

		return $no_error;
	}

	/**
	 * Recalculate takestamps of all albums in the database.
	 * This is expensive and not normally necessary so we only use it
	 * during migration.
	 */
	public static function reset_takestamp()
	{
		$albums = Album::get();
		foreach ($albums as $_album) {
			self::update_min_max_takestamp($_album);
			$_album->save();
		}
	}
}
