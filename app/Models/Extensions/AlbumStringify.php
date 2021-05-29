<?php

namespace App\Models\Extensions;

trait AlbumStringify
{
	/**
	 * Return parent_id as a string or null.
	 *
	 * @return string|null
	 */
	public function str_parent_id()
	{
		return $this->parent_id == null ? '' : strval($this->parent_id);
	}
}
