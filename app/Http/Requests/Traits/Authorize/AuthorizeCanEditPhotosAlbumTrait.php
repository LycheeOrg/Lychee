<?php

namespace App\Http\Requests\Traits\Authorize;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Determines if the user is authorized to modify the designated photos and album.
 */
trait AuthorizeCanEditPhotosAlbumTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album])) {
			return false;
		}

		/** @var Photo $photo */
		foreach ($this->photos as $photo) {
			if (!Gate::check(PhotoPolicy::CAN_EDIT, $photo)) {
				return false;
			}
		}

		return true;
	}
}