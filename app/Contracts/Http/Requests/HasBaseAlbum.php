<?php

namespace App\Contracts\Http\Requests;

use App\Models\Extensions\BaseAlbum;

interface HasBaseAlbum extends HasAbstractAlbum
{
	/**
	 * @return BaseAlbum|null
	 */
	public function album(): ?BaseAlbum;
}
