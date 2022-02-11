<?php

namespace App\Http\Requests\Contracts;

use App\Models\Album;

interface HasParentAlbum
{
	public const PARENT_ID_ATTRIBUTE = 'parent_id';

	/**
	 * @return Album|null
	 */
	public function parentAlbum(): ?Album;
}
