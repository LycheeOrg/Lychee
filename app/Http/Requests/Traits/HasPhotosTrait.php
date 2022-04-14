<?php

namespace App\Http\Requests\Traits;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

trait HasPhotosTrait
{
	/**
	 * @var Collection<Photo>
	 */
	protected Collection $photos;

	/**
	 * @return Collection<Photo>
	 */
	public function photos(): Collection
	{
		return $this->photos;
	}
}
