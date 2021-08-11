<?php

namespace App\Actions\Album;

use App\Models\BaseModelAlbumImpl;

class SetDescription extends Setter
{
	public function __construct()
	{
		parent::__construct(BaseModelAlbumImpl::query(), 'description');
	}
}
