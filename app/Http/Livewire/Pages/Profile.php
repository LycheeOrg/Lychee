<?php

namespace App\Http\Livewire\Pages;

use App\Enum\Livewire\PageMode;
use App\Models\Configs;
use Illuminate\View\View;
use Livewire\Component;

class Profile extends Component
{
	public PageMode $mode = PageMode::PROFILE;

	public bool $are_notification_active = false;

	public function mount()
	{
		$this->are_notification_active = Configs::getValueAsBool('new_photos_notification');
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.profile');
	}
}
