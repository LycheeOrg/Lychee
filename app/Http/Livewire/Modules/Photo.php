<?php

namespace App\Http\Livewire\Modules;

use App\Models\Photo as PhotoModel;
use Illuminate\View\View;
use Livewire\Component;

class Photo extends Component
{
	public PhotoModel $photo;

	/**
	 * @var Album
	 */
	// public Abst $album;

	public $visibleControls = false;

	public function mount(): void
	{
		// $this->album = $this->photo->album;
	}

	public function render(): View
	{
		// dd($this->photo);
		return view('livewire.pages.modules.photo');
	}
}
