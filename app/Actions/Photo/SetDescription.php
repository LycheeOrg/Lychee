<?php

namespace App\Actions\Photo;

class SetDescription extends Setter
{
	public function __construct()
	{
		$this->property = 'description';
	}
}
