<?php

namespace App\Actions\Album;

class SetLicense extends Setter
{
	public function __construct()
	{
		parent::__construct();
		$this->property = 'license';
	}
}
