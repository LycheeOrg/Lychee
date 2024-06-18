<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

interface HasPhotos
{
	/**
	 * @return Collection<int,Photo>
	 */
	public function photos(): Collection;
}
