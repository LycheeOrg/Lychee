<?php

namespace App\Http\Requests\Contracts;

interface HasParentAlbumID
{
	const PARENT_ID_ATTRIBUTE = 'parent_id';

	/**
	 * @return int|null
	 */
	public function parentID(): ?int;
}
