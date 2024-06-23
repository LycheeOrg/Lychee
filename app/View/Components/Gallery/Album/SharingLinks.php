<?php

namespace App\View\Components\Gallery\Album;

use Illuminate\View\Component;
use Illuminate\View\View;

class SharingLinks extends Component
{
	public string $twitter_link;
	public string $facebook_link;
	public string $mailTo_link;
	public string $url;
	public string $rawUrl;

	public function __construct(string $albumId, string $albumTitle)
	{
		$this->url = route('livewire-gallery-album', ['albumId' => $albumId]);
		$this->rawUrl = rawurlencode($this->url);
		$raw_title = rawurlencode($albumTitle);
		$this->twitter_link = 'https://twitter.com/share?url=' . $this->rawUrl;
		$this->facebook_link = 'https://www.facebook.com/sharer.php?u=' . $this->rawUrl . '?t=' . $raw_title;
		$this->mailTo_link = 'mailto:?subject=' . $raw_title . '&body=' . $this->rawUrl;
	}

	public function render(): View
	{
		return view('components.gallery.album.sharing-links');
	}
}