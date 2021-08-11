<?php

namespace App\Actions\Album;

use App\Models\BaseModelAlbumImpl;

class SetTitle extends Setters
{
	public function __construct()
	{
		parent::__construct(BaseModelAlbumImpl::query(), 'title');
	}
}
