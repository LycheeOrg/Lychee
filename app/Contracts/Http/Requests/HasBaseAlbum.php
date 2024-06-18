<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

use App\Models\Extensions\BaseAlbum;

interface HasBaseAlbum extends HasAbstractAlbum
{
	/**
	 * @return BaseAlbum|null
	 */
	public function album(): ?BaseAlbum;
}
