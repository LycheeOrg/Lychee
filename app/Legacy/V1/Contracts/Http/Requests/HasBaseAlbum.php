<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Models\Extensions\BaseAlbum;

interface HasBaseAlbum extends HasAbstractAlbum
{
	/**
	 * @return BaseAlbum|null
	 */
	public function album(): ?BaseAlbum;
}
