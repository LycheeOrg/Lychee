<?php

namespace App\Actions\Album;

class SetCover extends Setter
{
	public function __construct()
	{
		parent::__construct();
		$this->property = 'cover_id';
	}
}
