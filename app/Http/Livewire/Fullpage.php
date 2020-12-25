<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Fullpage extends Component
{
	/**
	 * @var
	 */
	public $mode;

	public function mount()
	{
		$this->mode = 'albums';
	}

	public function render()
	{
		return view('livewire.fullpage');
	}
}
