<?php

namespace App\Http\Requests\Traits;

trait HasParentAlbumIDTrait
{
	protected ?int $parentID = null;

	/**
	 * @return int|null
	 */
	public function parentID(): ?int
	{
		return $this->parentID;
	}
}
