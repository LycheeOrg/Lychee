<?php

namespace App\Contracts\Http\Requests;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

interface HasPhotos
{
	/**
	 * @return Collection<Photo>
	 */
	public function photos(): Collection;
}
