<?php

namespace App\Http\Requests\Traits;

trait HasAlbumIdsTrait
{
	/**
	 * @var string[]
	 */
	protected array $albumIds = [];

	/**
	 * @return string[]
	 */
	public function albumIds(): array
	{
		return $this->albumIds;
	}
}
