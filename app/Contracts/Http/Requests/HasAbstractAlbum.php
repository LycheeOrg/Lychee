<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

use App\Contracts\Models\AbstractAlbum;

interface HasAbstractAlbum
{
	/**
	 * @return AbstractAlbum|null
	 */
	public function album(): ?AbstractAlbum;
}
