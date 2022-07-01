<?php

namespace App\Http\Livewire\Components;

use App\Enum\PageMode;
use App\Http\Livewire\Traits\InteractWithModal;
use App\Models\Configs;
use Illuminate\View\View;
use Livewire\Component;

/**
 * We define here the header section of Lychee.
 * From here we can.
 */
class Header extends Component
{
	/*
	 * Add interaction with modal
	 */
	use InteractWithModal;

	/**
	 * @var string title in the header
	 */
	public string $title = '';

	/**
	 * @var PageMode
	 */
	public PageMode $mode;

	public function mount(PageMode $mode, ?string $title = null): void
	{
		$this->title = $title ?? Configs::getValueAsString('site_title');
		$this->mode = $mode;
	}

	/**
	 * Basic renderer.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.header.header');
	}

	/**
	 * Open a login modal box.
	 *
	 * @return void
	 */
	public function openLoginModal(): void
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
