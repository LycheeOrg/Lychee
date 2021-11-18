<?php

namespace App\Actions\Album;

use App\Models\BaseAlbumImpl;

class SetNSFW extends Setter
{
	public function __construct()
	{
		parent::__construct(BaseAlbumImpl::query(), 'is_nsfw');
	}
}
