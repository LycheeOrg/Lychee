<?php

namespace App\View\Components\Album;

use Illuminate\View\Component;

class Placeholder extends Component
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
	 */
	public function render()
	{
		return view('components.album.thumb-placeholder');
	}
}
