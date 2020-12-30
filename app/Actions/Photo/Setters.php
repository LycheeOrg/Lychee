<?php

namespace App\Actions\Photo;

use App\Models\Photo;

class Setters
{
	public $property;

	public function do(array $photoIDs, string $value): bool
	{
		return Photo::whereIn('id', $photoIDs)->update([$this->property => $value]);
	}
}
