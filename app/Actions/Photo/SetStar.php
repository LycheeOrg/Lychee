<?php

namespace App\Actions\Photo;

class SetStar extends Toggles
{
	public function __construct()
	{
		$this->property = 'star';
	}
}
