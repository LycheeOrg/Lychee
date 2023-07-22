<?php

namespace App\Livewire\Forms\Album;

use App\Enum\Livewire\AlbumMenuMode;
use App\Models\Extensions\BaseAlbum;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Menu extends Component
{
	public BaseAlbum $album;

	public string $mode = 'about';
	public int $userCount;

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param BaseAlbum $album to update the attributes of
	 *
	 * @return void
	 */
	public function mount(BaseAlbum $album): void
	{
		$this->album = $album;
		$this->userCount = User::count();
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.menu');
	}

	/**
	 * Set mode.
	 *
	 * @param string $mode
	 *
	 * @return void
	 */
	public function setMode(string $mode): void
	{
		$newMode = AlbumMenuMode::from($mode);

		$this->mode = $newMode->value;
	}
}
