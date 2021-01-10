<?php

namespace App\SmartAlbums;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class PublicAlbum extends SmartAlbum
{
	public $id = 'public';

	public function __construct()
	{
		parent::__construct();

		$this->title = 'public';
	}

	public function get_photos(): Builder
	{
		return Photo::public()->where(fn ($q) => $this->filter($q));
	}
}
