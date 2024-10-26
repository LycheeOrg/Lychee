<?php

namespace App\Http\Requests\Traits;

use App\Enum\PhotoLayoutType;

trait HasPhotoLayoutTrait
{
	protected ?PhotoLayoutType $photoLayout = null;

	/**
	 * @return PhotoLayoutType|null
	 */
	public function photoLayout(): ?PhotoLayoutType
	{
		return $this->photoLayout;
	}
}
