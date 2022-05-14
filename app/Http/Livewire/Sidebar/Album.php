<?php

namespace App\Http\Livewire\Sidebar;

use App\Contracts\AbstractAlbum;
use App\Models\Photo;
use App\Models\TagAlbum;
use Illuminate\View\View;
use Livewire\Component;

class Album extends Component
{
	public string $title;
	public string $description;
	public bool $is_tag_album = false;
	public string $showtags = '';

	public string $created_at;
	public int $children_count;
	public int $photo_count;
	public int $video_count;

	public string $sorting_col;
	public string $sorting_order;

	public bool $is_public;
	public bool $requires_link;
	public bool $is_downloadable;
	public bool $is_share_button_visible;
	public bool $has_password;

	public string $owner_name = '';
	public string $license;

	public function mount(AbstractAlbum $album): void
	{
		$this->load($album);
	}

	public function render(): View
	{
		return view('livewire.sidebar.album');
	}

	private function load(AbstractAlbum $album): void
	{
		// $this->album = $album;
		$this->title = $album->title;
		$this->description = $album->description ?? '';
		if ($album instanceof TagAlbum) {
			$this->is_tag_album = true;
			$this->showtags = $album->showtags;
		}
		$this->created_at = $album->created_at->format('F Y');

		$this->children_count = $album->children->count();

		$counted = $album->photos->countBy(function (Photo $photo) {
			return $photo->isVideo() ? 'videos' : 'photos';
		})->all();
		$this->photo_count = isset($counted['photos']) ? $counted['photos'] : 0;
		$this->video_count = isset($counted['videos']) ? $counted['videos'] : 0;
		$this->sorting_col = $album->sorting_col ?? '';
		$this->sorting_order = $album->sorting_order ?? '';

		$this->is_public = $album->is_public;
		$this->requires_link = $album->requires_link;
		$this->is_downloadable = $album->is_downloadable;
		$this->is_share_button_visible = $album->is_share_button_visible;
		$this->has_password = $album->has_password;

		$this->owner_name = $album->owner->name();

		$this->license = $album->license;
	}
}
