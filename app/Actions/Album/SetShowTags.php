<?php

namespace App\Actions\Album;

use App\Models\TagAlbum;

class SetShowTags extends Setter
{
	public function __construct()
	{
		parent::__construct(TagAlbum::query(), 'show_tags');
	}
}
