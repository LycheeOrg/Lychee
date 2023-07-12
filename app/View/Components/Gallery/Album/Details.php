<?php

namespace App\View\Components\Gallery\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Component;

class Details extends Component
{
	public string $album_id;
	public ?string $url;
	public string $title;
	public int $num_children = 0;
	public int $num_photos;
	public bool $can_download;
	public ?string $created_at = null;
	public ?string $description = null;

	public function __construct(AbstractAlbum $album, ?string $url)
	{
		$this->can_download = Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $album]);
		$this->url = $url;
		$this->title = $album->title;
		$this->album_id = $album->id;
		if ($album instanceof Album) {
			$this->num_children = $album->children()->count();
		}
		$this->num_photos = $album->photos()->count();
		if ($album instanceof BaseAlbum) {
			$this->created_at = $album->created_at->format('M j, Y g:i:s A e');
			$this->description = $album->description;
		}
	}

	public function render()
	{
		return view('components.gallery.album.details');
	}
}