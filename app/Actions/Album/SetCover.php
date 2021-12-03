<?php

namespace App\Actions\Album;

use App\Models\Album;

class SetCover extends Setter
{
	public function __construct()
	{
		parent::__construct(Album::query(), 'cover_id');
	}
}
