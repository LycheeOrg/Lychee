<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Contracts\Models\AbstractAlbum;

interface HasAbstractAlbum
{
	/**
	 * @return AbstractAlbum|null
	 */
	public function album(): ?AbstractAlbum;
}
