<?php

namespace App\Http\Requests\Traits;

use App\Models\Photo;

trait HasPhotoTrait
{
	protected ?Photo $photo = null;

	/**
	 * @return Photo|null
	 */
	public function photo(): ?Photo
	{
		return $this->photo;
	}
}
