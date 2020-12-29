<?php

namespace App\Actions\Album;

class SetTitle extends Setters
{
	public function __construct()
	{
		parent::__construct();
		$this->property = 'title';
	}
}
