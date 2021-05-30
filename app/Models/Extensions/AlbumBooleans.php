<?php

namespace App\Models\Extensions;

use App\Models\Configs;

trait AlbumBooleans
{
	/**
	 * Return whether or not an album is public.
	 *
	 * @return bool
	 */
	public function is_public()
	{
		return $this->public == 1;
	}

	/**
	 * Return whether or not public users will see the full photo.
	 *
	 * @return bool
	 */
	public function is_full_photo_visible()
	{
		if ($this->is_public()) {
			return $this->full_photo == 1;
		} else {
			return Configs::get_value('full_photo', '1') === '1';
		}
	}

	/**
	 * Return whether or not public users can download photos.
	 *
	 * @return bool
	 */
	public function is_downloadable(): bool
	{
		if ($this->is_public()) {
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
	public function is_share_button_visible(): bool
	{
		if ($this->is_public()) {
			return $this->share_button_visible == 1;
		} else {
			return Configs::get_value('share_button_visible', '0') === '1';
		}
	}

	public function is_smart()
	{
		return $this->smart;
	}

	public function is_tag_album()
	{
		return $this->smart && !empty($this->showtags);
	}
}
