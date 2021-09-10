<?php

namespace App\Actions\Album;

use App\Models\BaseAlbumImpl;

class SetTitle extends Setters
{
	public function __construct()
	{
		parent::__construct(BaseAlbumImpl::query(), 'title');
	}
}
