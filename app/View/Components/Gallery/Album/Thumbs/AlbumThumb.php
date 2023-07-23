<?php

namespace App\View\Components\Gallery\Album\Thumbs;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class AlbumThumb extends Component
{
	public bool $isVideo;
	public string $class;
	public string $src = '';
	public string $dataSrc = '';
	public string $dataSrcSet = '';

	public function __construct(
		string $type = '',
		string $thumb = '',
		string $thumb2x = '',
		string $class = ''
	) {
		$this->isVideo = Str::contains($type, 'video');

		$this->class = $class . ($this->isVideo ? ' video' : '');

		if ($thumb === 'uploads/thumb/') {
			$this->src = $this->isVideo ? URL::asset('img/play-icon.png') : URL::asset('img/placeholder.png');
			$this->dataSrc = Str::contains($type, 'raw') ? URL::asset('img/no_images.svg') : '';
		} else {
			$this->src = URL::asset('img/no_images.svg');

			if ($thumb !== '') {
				$this->dataSrc = URL::asset($thumb);
			}
		}

		$this->src = sprintf("src='%s'", $this->src);
		if ($this->dataSrc !== '') {
			$this->dataSrc = sprintf("data-src='%s'", $this->dataSrc);
		}

		if ($thumb2x !== '') {
			$this->dataSrcSet = sprintf("data-srcset='%s 2x'", URL::asset($thumb2x));
		}
	}

	public function render()
	{
		return view('components.gallery.album.thumbs.album-thumb');
	}
}
