<?php

namespace App\View\Components;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\Component;

class Modal extends Component
{
	/**
	 * Create a new component instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
	 * Get the view / contents that represent the component.
	 *
	 * @return \Illuminate\Contracts\View\View|\Closure|string
	 *
	 * @throws BindingResolutionException
	 */
	public function render()
	{
		return view('components.modal');
	}
}
