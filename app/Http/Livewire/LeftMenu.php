<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\Container\BindingResolutionException;
use Livewire\Component;

class LeftMenu extends Component
{
	/**
	 * @throws BindingResolutionException
	 */
	public function render()
	{
		return view('livewire.left-menu');
	}
}
