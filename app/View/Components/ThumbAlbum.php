<?php

namespace App\View\Components;

use Illuminate\Support\Str;
use Illuminate\View\Component;

class ThumbAlbum extends Component
{
	public $isVideo;
	public $type;
	public $thumb;
	public $thumb2x;

	/**
	 * Create a new component instance.
	 *
	 * @return void
	 */
	public function __construct($type = '', $thumb = '', $thumb2x = '')
	{
		$this->isVideo = Str::contains($type, 'video');
		$this->thumb = $thumb;
		$this->thumb2x = $thumb2x;
		$this->type = $type;
	}

	/**
	 * Get the view / contents that represent the component.
	 *
	 * @return \Illuminate\Contracts\View\View|\Closure|string
	 */
	public function render()
	{
		if ($this->type == '') {
			return view('components.thumb-placeholder');
		}
		if ($this->thumb == 'uploads/thumb/' && $this->isVideo) {
			return view('components.thumb-play');
		}
		if ($this->thumb == 'uploads/thumb/' && Str::contains($this->type, 'raw')) {
			return view('components.thumb-placeholder');
		}

		return view('components.thumb-album');
	}
}
