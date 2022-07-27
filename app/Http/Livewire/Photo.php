<?php

namespace App\Http\Livewire;

use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Models\Album as AlbumModel;
use App\Models\Photo as PhotoModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Livewire\Component;

class Photo extends Component
{
	public PhotoModel $photo;
	public AlbumModel $album;
	public array $data;
	public bool $visibleControls = false;

	public function mount(PhotoModel $photo)
	{
		$this->album = $photo->album;
		$this->photo = $photo;
	}

	/**
	 * @throws BindingResolutionException
	 * @throws IllegalOrderOfOperationException
	 */
	public function render()
	{
		$this->data = $this->photo->toArray();

		return view('livewire.photo');
	}
}
