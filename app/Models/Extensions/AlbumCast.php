<?php

namespace App\Models\Extensions;

use App\Assets\Helpers;
use App\SmartAlbums\TagAlbum;

trait AlbumCast
{
	use AlbumBooleans;
	use AlbumStringify;

	/**
	 * Returns album-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array
	 */
	public function toReturnArray(): array
	{
		$return = [
			'id' => strval($this->id),
			'title' => $this->title,
			'public' => strval($this->public),
			'full_photo' => Helpers::str_of_bool($this->is_full_photo_visible()),
			'visible' => strval($this->viewable),
			'nsfw' => strval($this->nsfw),
			'parent_id' => $this->str_parent_id(),
			'description' => strval($this->description),

			'downloadable' => Helpers::str_of_bool($this->is_downloadable()),
			'share_button_visible' => Helpers::str_of_bool($this->is_share_button_visible()),

			// Parse date
			'sysdate' => $this->created_at->format('F Y'),
			'min_takestamp' => $this->str_min_takestamp(),
			'max_takestamp' => $this->str_max_takestamp(),

			// Parse password
			'password' => Helpers::str_of_bool($this->password != ''),
			'license' => $this->get_license(),

			// Parse Ordering
			'sorting_col' => $this->sorting_col,
			'sorting_order' => $this->sorting_order,

			'thumbs' => [],
			'thumbs2x' => [],
			'types' => [],
			'has_albums' => Helpers::str_of_bool($this->isLeaf() === false),
		];

		if ($this->is_tag_album()) {
			$return['tag_album'] = '1';
			$return['show_tags'] = $this->showtags;
		}

		if (!empty($this->showtags) || !$this->smart) {
			$return['owner'] = $this->owner->name();
		}

		return $return;
	}

	public function toTagAlbum(): TagAlbum
	{
		$tag_album = resolve(TagAlbum::class);
		$tag_album->id = $this->id;
		$tag_album->title = $this->title;
		$tag_album->owner_id = $this->owner_id;
		$tag_album->parent_id = $this->parent_id;
		$tag_album->_lft = $this->_lft;
		$tag_album->_rgt = $this->_rgt;
		$tag_album->description = $this->description ?? '';
		$tag_album->min_takestamp = $this->min_takestamp;
		$tag_album->max_takestamp = $this->max_takestamp;
		$tag_album->public = $this->public;
		$tag_album->full_photo = $this->full_photo;
		$tag_album->viewable = $this->viewable;
		$tag_album->nsfw = $this->nsfw;
		$tag_album->downloadable = $this->downloadable;
		$tag_album->password = $this->password;
		$tag_album->license = $this->license;
		$tag_album->created_at = $this->created_at;
		$tag_album->updated_at = $this->updated_at;
		$tag_album->share_button_visible = $this->share_button_visible;
		$tag_album->smart = $this->smart;
		$tag_album->showtags = $this->showtags;

		return $tag_album;
	}
}
