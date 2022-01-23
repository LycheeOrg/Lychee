<?php

namespace App\Http\Livewire;

use App\Models\Photo as PhotoModel;
use Livewire\Component;

class Photo extends Component
{
	/**
	 * @var PhotoModel
	 */
	public $photo;

	/**
	 * @var Album
	 */
	public $album;

	/**
	 * @var array (for now)
	 */
	public $data;

	public $visibleControls = false;

	public function mount()
	{
		$this->album = $this->photo->album;
	}

	public function render()
	{
		$this->data = $this->photo->toArray();

		return view('livewire.photo');
	}
}
