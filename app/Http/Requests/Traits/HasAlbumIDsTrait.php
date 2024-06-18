<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

trait HasAlbumIDsTrait
{
	/**
	 * @var string[]
	 */
	protected array $albumIDs = [];

	/**
	 * @return string[]
	 */
	public function albumIDs(): array
	{
		return $this->albumIDs;
	}
}
