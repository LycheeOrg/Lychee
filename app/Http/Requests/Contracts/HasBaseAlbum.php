<?php

namespace App\Http\Requests\Contracts;

use App\Models\Extensions\BaseAlbum;

interface HasBaseAlbum extends HasAbstractAlbum
{
	/**
	 * @return BaseAlbum|null
	 */
	public function album(): ?BaseAlbum;
}
