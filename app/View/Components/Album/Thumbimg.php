<?php

namespace App\View\Components\Album;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Thumbimg extends Component
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
		// TODO: Don't query the MIME type directly; use the methods of Photo or MediaFile
		$this->isVideo = Str::contains($type, 'video');
		$this->thumb = $thumb;
		$this->thumb2x = $thumb2x;
		$this->type = $type;
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
		// TODO: Don't hardcode paths
		if ($this->thumb == 'uploads/thumb/' && $this->isVideo) {
			return view('components.album.thumb-play');
		}
		// TODO: Don't query the MIME type directly; use the methods of Photo or MediaFile
		if ($this->thumb == 'uploads/thumb/' && Str::contains($this->type, 'raw')) {
			return view('components.album.thumb-placeholder');
		}

		return view('components.album.thumbimg');
	}
}
