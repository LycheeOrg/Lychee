<?php

namespace App\Http\Requests\Traits;

trait HasParentAlbumIDTrait
{
	protected ?string $parentID = null;

	/**
	 * @return string|null
	 */
	public function parentID(): ?string
	{
		return $this->parentID;
	}
}
