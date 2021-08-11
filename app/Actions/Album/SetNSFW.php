<?php

namespace App\Actions\Album;

use App\Models\BaseModelAlbumImpl;

class SetNSFW extends Setter
{
	public function __construct()
	{
		parent::__construct(BaseModelAlbumImpl::query(), 'nsfw');
	}
}
