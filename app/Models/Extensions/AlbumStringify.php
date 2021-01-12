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
}
