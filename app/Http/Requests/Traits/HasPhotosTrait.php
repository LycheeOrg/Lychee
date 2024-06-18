<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

trait HasPhotosTrait
{
	/**
	 * @var Collection<int,Photo>
	 */
	protected Collection $photos;

	/**
	 * @return Collection<int,Photo>
	 */
	public function photos(): Collection
	{
		return $this->photos;
	}
}
