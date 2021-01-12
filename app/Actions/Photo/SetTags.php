<?php

namespace App\Actions\Photo;

class SetTags extends Setters
{
	public function __construct()
	{
		$this->property = 'tags';
	}
}
