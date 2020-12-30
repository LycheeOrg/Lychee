<?php

namespace App\Actions\Photo;

class SetTitle extends Setters
{
	public function __construct()
	{
		$this->property = 'title';
	}
}
