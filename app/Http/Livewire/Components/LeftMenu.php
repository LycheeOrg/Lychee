<?php

namespace App\Http\Livewire\Components;

use App\Facades\AccessControl;
use Barryvdh\Debugbar\Facades\Debugbar;
use Livewire\Component;

class LeftMenu extends Component
{
	public bool $isOpen = false;

	protected $listeners = [
		'open' => 'open',
		'close' => 'close',
	];

	public function open()
	{
		Debugbar::warning('request to open left menu');
		$this->isOpen = true;
	}

	public function close()
	{
		Debugbar::warning('request to close left menu');
		$this->isOpen = false;
	}

	public function logout()
	{
		AccessControl::logout();

		return redirect('/livewire/');
	}

	public function render()
	{
		return view('livewire.left-menu');
	}
}
