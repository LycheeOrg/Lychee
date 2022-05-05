<?php

namespace App\Http\Livewire\Components\Base;

use Barryvdh\Debugbar\Facades\Debugbar;
use Livewire\Component;

class Openable extends Component
{
	public bool $isOpen = false;

	protected $listeners = [
		'open',
		'close',
		'toggle',
	];

	public function open()
	{
		Debugbar::info('request to open.');
		$this->isOpen = true;
	}

	public function close()
	{
		Debugbar::info('request to close.');
		$this->isOpen = false;
	}

	public function toggle()
	{
		Debugbar::info('toggle.');
		$this->isOpen = !$this->isOpen;
	}
}
