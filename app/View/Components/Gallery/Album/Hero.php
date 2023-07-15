<?php

namespace App\View\Components\Gallery\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Extensions\BaseAlbum;
use Illuminate\View\Component;

class Hero extends Component
{
	public string $url;
	public string $title;
	public ?string $min_taken_at = null;
	public ?string $max_taken_at = null;

	public function __construct(AbstractAlbum $album, string $url)
	{
		$this->url = $url;
		$this->title = $album->title;
		if ($album instanceof BaseAlbum) {
			// Todo: add date format configuration
			$this->min_taken_at = $album->min_taken_at?->format('M Y');
			$this->max_taken_at = $album->max_taken_at?->format('M Y');
		}
	}

	public function render()
	{
		return view('components.gallery.album.hero');
	}
}