<?php

namespace App\Actions\Album;

use App\Exceptions\JsonError;

class SetCover extends Setter
{
	public function __construct()
	{
		parent::__construct();
		$this->property = 'cover_id';
	}

	public function do(string $albumID, string $value): bool
	{
		if ($this->albumFactory->is_smart($albumID)) {
			throw new JsonError('This is not possible for Smart albums.');
		}

		$album = $this->albumFactory->make($albumID);

		return $this->execute($album, $value);
	}
}
