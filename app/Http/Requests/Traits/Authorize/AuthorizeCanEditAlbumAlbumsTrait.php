<?php

namespace App\Http\Requests\Traits\Authorize;

use App\Contracts\Models\AbstractAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

trait AuthorizeCanEditAlbumAlbumsTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album])) {
			return false;
		}

		/** @var AbstractAlbum $album */
		foreach ($this->albums as $album) {
			if (!Gate::check(AlbumPolicy::CAN_EDIT, $album)) {
				return false;
			}
		}

		return true;
	}
}