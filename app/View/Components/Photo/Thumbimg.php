<?php

namespace App\View\Components\Photo;

use Illuminate\Support\Facades\URL;
use Illuminate\View\Component;

class Thumbimg extends Component
{
	public $class;
	public $src;
	public $srcset;
	public $srcset2x;

	/**
	 * Create a new component instance.
	 *
	 * @return void
	 */
	public function __construct($class, $thumb, $thumb2x = '', $type = '', $dim = '', $dim2x = '')
	{
		$this->class = $class;
		$this->src = "src='" . URL::asset('img/placeholder.png') . "'";
		$this->srcset = "data-src='" . URL::asset($thumb) . "'";
		$thumb2x_src = '';

		if ($type == 'square') {
			$thumb2x_src = URL::asset($thumb2x) . ' 2x';
		} else {
			$thumb2x_src = URL::asset($thumb) . ' ' . $dim . 'w, ';
			$thumb2x_src .= URL::asset($thumb2x) . ' ' . $dim2x . 'w';
		}

		$this->srcset2x = $thumb2x != '' ? "data-srcset='" . $thumb2x_src . "'" : '';
	}

	/**
	 * Get the view / contents that represent the component.
	 *
	 * @return \Illuminate\Contracts\View\View|\Closure|string
	 */
	public function render()
	{
		return view('components.photo.thumbimg');
	}
}
