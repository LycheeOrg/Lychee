<?php

namespace App\Http\Requests\Contracts;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

interface HasPhotos
{
	public const PHOTO_IDS_ATTRIBUTE = 'photoIDs';

	/**
	 * @return Collection<Photo>
	 */
	public function photos(): Collection;
}
