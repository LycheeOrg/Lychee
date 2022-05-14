<?php

namespace App\Http\Livewire\Components;

use App\Http\Livewire\Traits\InteractWithModal;
use App\Models\Configs;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Livewire\Component;

class Header extends Component
{
	use InteractWithModal;

	/**
	 * @var string
	 */
	public string $title = '';

	/**
	 * @var string
	 */
	public string $mode;

	public function mount(?string $mode = 'albums', ?string $title = null): void
	{
		$this->title = $title ?? Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		$this->mode = $mode ?? 'albums';
	}

	public function render(): View
	{
		return view('livewire.header.header');
	}

	public function login(): void
	{
		$this->openModal('forms.login');
	}

	public function openLeftMenu(): void
	{
		$this->emitTo('components.left-menu', 'open');
	}

	public function toggleSideBar(): void
	{
		$this->emitTo('components.sidebar', 'toggle');
	}
}
