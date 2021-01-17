<?php

namespace App\Actions\Album;

class SetCover extends Setter
{
	public function __construct()
	{
		parent::__construct();
		//FINAL
//		$this->property = 'cover';
		//TEST
		$this->property = 'description';
	}
}
