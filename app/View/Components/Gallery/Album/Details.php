<?php

namespace App\View\Components\Gallery\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
use App\Models\Configs;
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
	public int $num_photos = 0;
	public bool $can_download;
	public ?string $created_at = null;
	public ?string $description = null;

	public function __construct(AbstractAlbum $album, ?string $url)
	{
		$date_format = Configs::getValueAsString('date_format_hero_created_at');
		$this->can_download = Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $album]);
		$this->url = $url;
		$this->title = $album->title;
		$this->album_id = $album->id;
		if ($album instanceof Album) {
			$this->num_children = $album->num_children;
			// TODO fix me later
			$this->num_photos = $album->num_photos;
		}
		if ($album instanceof BaseAlbum) {
			$this->created_at = $album->created_at->format($date_format);
			$this->description = $album->description;
		}
	}

	public function render()
	{
		return view('components.gallery.album.details');
	}
}