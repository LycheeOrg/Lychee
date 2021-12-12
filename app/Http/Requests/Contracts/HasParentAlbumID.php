<?php

namespace App\Http\Requests\Contracts;

interface HasParentAlbumID
{
	public const PARENT_ID_ATTRIBUTE = 'parent_id';

	/**
	 * @return string|null
	 */
	public function parentID(): ?string;
}
