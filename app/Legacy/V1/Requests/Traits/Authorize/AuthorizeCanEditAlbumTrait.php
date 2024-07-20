<?php

namespace App\Legacy\V1\Requests\Traits\Authorize;

use App\Contracts\Models\AbstractAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Determines if the user is authorized to modify or write into the
 * designated album.
 */
trait AuthorizeCanEditAlbumTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]);
	}
}