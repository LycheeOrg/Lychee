<?php

namespace App\Http\Livewire\Components;

use App\Http\Livewire\Traits\InteractWithModal;
use App\Models\Configs;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class Header extends Component
{
	use InteractWithModal;

	/**
	 * @var string
	 */
	public $title = '';

	/**
	 * @var string
	 */
	public $mode;

	public function mount(?string $mode = 'albums', ?string $title = null)
	{
		$this->title = $title ?? Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		$this->mode = $mode ?? 'albums';
	}

	public function render()
	{
		return view('livewire.header.header');
	}

	public function login()
	{
		$this->openModal('forms.login');
	}

	public function openLeftMenu()
	{
		$this->emitTo('components.left-menu', 'open');
	}
}

