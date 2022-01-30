<?php

namespace App\Http\Livewire;

use App\Models\Photo as PhotoModel;
use Livewire\Component;

class Photo extends Component
{
	public PhotoModel $photo;

	/**
	 * @var Album
	 */
	// public Abst $album;

	public $visibleControls = false;

	public function mount()
	{
		// $this->album = $this->photo->album;
	}

	public function render()
	{
		// dd($this->photo);
		return view('livewire.photo');
	}
}
