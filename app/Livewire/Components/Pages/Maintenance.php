<?php

declare(strict_types=1);

namespace App\Livewire\Components\Pages;

use App\Livewire\Components\Menus\LeftMenu;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Admin page to manage lychee install and other commands.
 */
class Maintenance extends Component
{
	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		Gate::authorize(SettingsPolicy::CAN_UPDATE, Configs::class);

		return view('livewire.pages.maintenance');
	}

	public function back(): mixed
	{
		$this->dispatch('closeLeftMenu')->to(LeftMenu::class);

		return $this->redirect(route('livewire-gallery'), true);
	}
}
