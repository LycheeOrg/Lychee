<?php

namespace App\Http\Livewire;

use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Livewire\Component;

class Header extends Component
{
	public string $title = '';
	public string $mode;

	public function mount(?string $mode = 'albums', $album = null)
	{
		$this->title = Configs::getValueAsString('site_title');
		if ($album !== null) {
			$this->title = $album->title;
		}
		$this->mode = $mode ?? 'albums';
	}

	/**
	 * @throws BindingResolutionException
	 */
	public function render()
	{
		return view('livewire.header');
	}
}
