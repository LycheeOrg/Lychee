<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Strategies\RotateStrategy;
use App\Models\Photo;

class Rotate
{
	public function do(Photo $photo, int $direction): Photo
	{
		return (new RotateStrategy($photo, $direction))->do();
	}
}
