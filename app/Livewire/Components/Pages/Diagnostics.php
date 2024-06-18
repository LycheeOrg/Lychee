<?php

declare(strict_types=1);

namespace App\Livewire\Components\Pages;

use App\Livewire\Components\Menus\LeftMenu;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Diagnostics extends Component
{
	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.diagnostics');
	}

	public function back(): mixed
	{
		$this->dispatch('closeLeftMenu')->to(LeftMenu::class);

		return $this->redirect(route('livewire-gallery'), true);
	}

	#[On('reloadPage')]
	public function reloadPage(): void
	{
		$this->render();
	}
}
