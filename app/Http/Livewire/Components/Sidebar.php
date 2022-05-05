<?php

namespace App\Http\Livewire\Components;

use App\Contracts\AbstractAlbum;
use App\Http\Livewire\Components\Base\Openable;
use App\Http\Livewire\Traits\AlbumProperty;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\SmartAlbums\BaseSmartAlbum;

class Sidebar extends Openable
{
	use AlbumProperty;

	// public ?Photo $photo = null;
	public ?BaseAlbum $baseAlbum = null;
	public ?BaseSmartAlbum $smartAlbum = null;

	public function mount(?AbstractAlbum $album = null, ?Photo $photo = null)
	{
		$this->loadAlbum($album);
	}

	public function render()
	{
		return view('livewire.sidebar');
	}
}