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
		$this->public = false;
	}

	public function get_photos(): Builder
	{
		return Photo::unsorted()->where(fn ($q) => $this->filter($q));
	}
}
