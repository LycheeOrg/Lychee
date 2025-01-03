<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

interface HasPhotos
{
	/**
	 * @return Collection<int,Photo>
	 */
	public function photos(): Collection;
}
