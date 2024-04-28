<?php

namespace App\Contracts\PhotoCreate;

use App\Models\Photo;

interface PhotoDTO
{
	public function getPhoto(): Photo;
}
