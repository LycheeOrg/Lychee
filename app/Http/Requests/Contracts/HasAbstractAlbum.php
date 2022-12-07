<?php

namespace App\Http\Requests\Contracts;

use App\Contracts\AbstractAlbum;

interface HasAbstractAlbum
{
	/**
	 * @return AbstractAlbum|null
	 */
	public function album(): ?AbstractAlbum;
}
