<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

use App\Contracts\Models\AbstractAlbum;

trait HasAbstractAlbumTrait
{
	protected ?AbstractAlbum $album = null;

	/**
	 * @return AbstractAlbum|null
	 */
	public function album(): ?AbstractAlbum
	{
		return $this->album;
	}
}
