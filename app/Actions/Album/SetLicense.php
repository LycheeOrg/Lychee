<?php

namespace App\Actions\Album;

use App\Models\Album;

class SetLicense extends Setter
{
	public function __construct()
	{
		parent::__construct(Album::query(), 'license');
	}
}
