<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions\AlbumActions;

use App\Assets\Helpers;
use App\ModelFunctions\PhotoActions\Cast as PhotoCast;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Album;
use App\Models\Configs;
use App\SmartAlbums\TagAlbum;
use Illuminate\Support\Collection as BaseCollection;

class Cast
{
	/**
	 * Returns album-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array
	 */
	public static function toArray(Album $album): array
	{
		$return = [
			'id' => strval($album->id),
			'title' => $album->title,
			'public' => strval($album->public),
			'full_photo' => Helpers::str_of_bool($album->is_full_photo_visible()),
			'visible' => strval($album->viewable),
			'nsfw' => strval($album->nsfw),
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

			// Parse Ordering
			'sorting_col' => $album->sorting_col,
			'sorting_order' => $album->sorting_order,

			'thumbs' => [],
			'thumbs2x' => [],
			'types' => [],
		];

		if ($album->smart && !empty($album->showtags)) {
			$return['tag_album'] = '1';
			$return['show_tags'] = $album->showtags;
		}

		return $return;
	}

	public static function toTagAlbum(Album $album): TagAlbum
	{
		$tag_album = resolve(TagAlbum::class);
		$tag_album->id = $album->id;
		$tag_album->title = $album->title;
		$tag_album->owner_id = $album->owner_id;
		$tag_album->parent_id = $album->parent_id;
		$tag_album->description = $album->description;
		$tag_album->min_takestamp = $album->min_takestamp;
		$tag_album->max_takestamp = $album->max_takestamp;
		$tag_album->public = $album->public;
		$tag_album->full_photo = $album->full_photo;
		$tag_album->viewable = $album->viewable;
		$tag_album->nsfw = $album->nsfw;
		$tag_album->downloadable = $album->downloadable;
		$tag_album->password = $album->password;
		$tag_album->license = $album->license;
		$tag_album->created_at = $album->created_at;
		$tag_album->updated_at = $album->updated_at;
		$tag_album->share_button_visible = $album->share_button_visible;
		$tag_album->smart = $album->smart;
		$tag_album->showtags = $album->showtags;

		return $tag_album;
	}

	public static function toArrayWith(Album $album, BaseCollection $children)
	{
		$album_array = self::toArray($album);

		$album_array['albums'] = $children->map(fn ($e) => self::toArrayWith($e[0], $e[1]))->values();
		// we need values because we need to reset the keys for when logged in.

		return $album_array;
	}

	/**
	 * Set up the wrap arround of the photos if setting is true and if there are enough pictures.
	 */
	public static function wrapAroundPhotos(array &$return_photos): void
	{
		$photo_counter = count($return_photos);

		if ($photo_counter > 1 && Configs::get_value('photos_wraparound', '1') === '1') {
			// Enable next and previous for the first and last photo
			$lastElement = end($return_photos);
			$lastElementId = $lastElement['id'];
			$firstElement = reset($return_photos);
			$firstElementId = $firstElement['id'];

			$return_photos[$photo_counter - 1]['nextPhoto'] = $firstElementId;
			$return_photos[0]['previousPhoto'] = $lastElementId;
		}
	}

	/**
	 * Given an Album, return the thumbs of its 3 first pictures (excluding subalbums).
	 */
	public static function getThumbs(array &$return, Album $album, SymLinkFunctions $symLinkFunctions): void
	{
		$photos = $album->get_photos()->get();
		$return['thumbs'] = [];
		$return['thumbs2x'] = [];
		$return['types'] = [];
		$return['num'] = strval($photos->count());

		$k = 0;
		foreach ($photos as $photo) {
			if ($k < 3) {
				$ret = PhotoCast::toThumb($photo, $symLinkFunctions);
				$ret->insertToArrays($return['thumbs'], $return['types'], $return['thumbs2x']);
				$k++;
			} else {
				break;
			}
		}
	}
}
