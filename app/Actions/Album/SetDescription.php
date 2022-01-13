<?php

namespace App\Actions\Album;

use App\Models\BaseAlbumImpl;

class SetDescription extends Setter
{
	public function __construct()
	{
		parent::__construct(BaseAlbumImpl::query(), 'description');
	}
}
