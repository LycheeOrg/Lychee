<?php

namespace App\Actions\Photo;

class SetPublic extends Toggle
{
	public function __construct()
	{
		$this->property = 'public';
	}
}
