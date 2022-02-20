<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Icon extends Component
{
	public $class;
	public $icon;

	/**
	 * Create a new component instance.
	 *
	 * @return void
	 */
	public function __construct($icon, $class)
	{
		$this->class = $class;
		$this->icon = $icon;
	}

	/**
	 * Get the view / contents that represent the component.
	 *
	 * @return \Illuminate\Contracts\View\View|\Closure|string
	 */
	public function render()
	{
		return view('components.icons.icon');
	}
}
