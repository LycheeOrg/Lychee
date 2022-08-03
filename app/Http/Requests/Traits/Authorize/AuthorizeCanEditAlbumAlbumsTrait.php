<?php

namespace App\Http\Requests\Traits\Authorize;

use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

trait AuthorizeCanEditAlbumAlbumsTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, $this->album ?? Album::class) &&
			$this->authorizeAlbumsWrite($this->albums);
	}
}