<?php

namespace App\Actions\Album;

use App\Models\Album;

class Setters extends Action
{
	public $property;

	public function do(array $albumIDs, string $value): bool
	{
		return Album::whereIn('id', $albumIDs)->update([$this->property => $value]);
	}
}
