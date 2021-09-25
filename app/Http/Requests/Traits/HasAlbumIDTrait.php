<?php

namespace App\Http\Requests\Traits;

trait HasAlbumIDTrait
{
	/**
	 * @var string|int|null
	 */
	protected $albumID = null;

	/**
	 * @return string|int|null
	 */
	public function albumID()
	{
		return $this->albumID;
	}
}
