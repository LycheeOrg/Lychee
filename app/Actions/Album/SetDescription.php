<?php

namespace App\Actions\Album;

class SetDescription extends Setter
{
	public function __construct()
	{
		$this->property = 'description';
	}
}
