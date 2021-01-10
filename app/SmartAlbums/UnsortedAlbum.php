<?php

namespace App\SmartAlbums;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class UnsortedAlbum extends SmartAlbum
{
	public $id = 'unsorted';

	public function __construct()
	{
		parent::__construct();

		$this->title = 'unsorted';
	}

	public function get_photos(): Builder
	{
		return Photo::unsorted()->where(fn ($q) => $this->filter($q));
	}

	public function is_public()
	{
		return false;
	}
}
