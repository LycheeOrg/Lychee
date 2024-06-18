<?php

declare(strict_types=1);

namespace App\Contracts\PhotoCreate;

use App\Models\Photo;

interface PhotoDTO
{
	public function getPhoto(): Photo;
}
