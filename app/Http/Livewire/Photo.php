<?php

namespace App\Http\Livewire;

use App\Actions\Photo\Prepare;
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

	/**
	 * @var Prepare
	 */
	private $prepare;

	public function mount(PhotoModel $photo, Prepare $prepare)
	{
		$this->album = $photo->album;
		$this->photo = $photo;
		$this->prepare = $prepare;
	}

	public function render()
	{
		$this->data = $this->prepare->do($this->photo);

		return view('livewire.photo');
	}
}
