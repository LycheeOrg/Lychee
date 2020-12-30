<?php

namespace App\Actions\Photo;

class SetLicense extends Setter
{
	public function __construct()
	{
		$this->property = 'license';
	}
}
