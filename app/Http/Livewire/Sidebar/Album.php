<?php

namespace App\Http\Livewire\Sidebar;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumProtectionPolicy;
use App\Models\Album as ModelsAlbum;
use App\Models\Photo;
use App\Models\TagAlbum;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\View;
use Livewire\Component;

/**
 * This is the side bar in the case of Album.
 *
 * Contrary to the JS implementation, the attributes are directly embeded in the bar.
 * This will (hopefully) simplify the update when editing properties.
 */
class Album extends Component
{
	public string $title;
	public string $description;
	public bool $is_tag_album = false;
	/** @var string[] */
	public array $showtags;

	public string $created_at;
	public int $children_count;
	public int $photo_count;
	public int $video_count;

	public string $sorting_col;
	public string $sorting_order;

	protected ?AlbumProtectionPolicy $policy = null;

	public string $owner_name = '';
	public string $license;

	/**
	 * Given an album we load the attributes.
	 *
	 * @param AbstractAlbum $album
	 *
	 * @return void
	 */
	public function mount(AbstractAlbum $album): void
	{
		$this->load($album);
	}

	/**
	 * Rendering of he template.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('livewire.sidebar.album');
	}

	/**
	 * Here is where we assign the attributes given an album.
	 *
	 * It is more interesting to extract this code because the code of mount() is executed only once.
	 * On the other hand, the load() can be called from other components before triggering a rerendering upon updating properties.
	 *
	 * @param AbstractAlbum $album
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	private function load(AbstractAlbum $album): void
	{
		// $this->album = $album;
		$this->title = $album->title;

		if ($album instanceof ModelsAlbum) {
			$this->description = $album->description ?? '';
			$this->children_count = $album->num_children;
			$this->sorting_col = $album->sorting_col ?? '';
			$this->sorting_order = $album->sorting_order ?? '';
			$this->owner_name = $album->owner->name;
			$this->license = $album->license;
		} else {
			$this->description = '';
		}

		if ($album instanceof TagAlbum) {
			$this->is_tag_album = true;
			$this->showtags = $album->show_tags;
		}

		if ($album instanceof ModelsAlbum || $album instanceof TagAlbum) {
			$this->created_at = $album->created_at->format('F Y');
			$this->policy = $album->policy;
		}

		$counted = $album->photos->countBy(function (Photo $photo) {
			return $photo->isVideo() ? 'videos' : 'photos';
		})->all();
		$this->photo_count = isset($counted['photos']) ? $counted['photos'] : 0;
		$this->video_count = isset($counted['videos']) ? $counted['videos'] : 0;
	}

	/**
	 * Album property to support the multiple type.
	 *
	 * @return AlbumProtectionPolicy
	 */
	public function getPolicyProperty(): AlbumProtectionPolicy
	{
		return $this->policy ?? AlbumProtectionPolicy::ofDefaultPrivate();
	}
}
