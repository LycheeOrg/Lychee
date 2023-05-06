<?php

namespace App\Http\Livewire\Modules\Sidebar;

use App\DTO\AlbumProtectionPolicy;
use App\Models\Album as ModelsAlbum;
use App\Models\Extensions\BaseAlbum;
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

	public string $sorting_col = '';
	public string $sorting_order = '';

	protected ?AlbumProtectionPolicy $policy = null;

	public string $owner_name = '';
	public string $license;

	/**
	 * Mount the album Side bar.
	 *
	 * @param BaseAlbum $album
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function mount(BaseAlbum $album): void
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
		return view('livewire.modules.sidebar.album');
	}

	/**
	 * Here is where we assign the attributes given an album.
	 *
	 * It is more interesting to extract this code because the code of mount() is executed only once.
	 * On the other hand, the load() can be called from other components before triggering a rerendering upon updating properties.
	 *
	 * @param BaseAlbum $baseAlbum
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	private function load(BaseAlbum $baseAlbum): void
	{
		// $this->album = $album;
		$this->title = $baseAlbum->title;

		if ($baseAlbum instanceof ModelsAlbum) {
			$this->description = $baseAlbum->description ?? '';
			$this->children_count = $baseAlbum->num_children;
			$this->license = $baseAlbum->license;
			$this->sorting_col = $baseAlbum->sorting_col ?? '';
			$this->sorting_order = $baseAlbum->sorting_order ?? '';
		} else {
			$this->description = '';
		}
		$this->owner_name = $baseAlbum->owner->name;

		if ($baseAlbum instanceof TagAlbum) {
			$this->is_tag_album = true;
			$this->showtags = $baseAlbum->show_tags;
		}

		$this->created_at = $baseAlbum->created_at->format('F Y');
		$this->policy = $baseAlbum->policy;

		/** @phpstan-ignore-next-line */
		$counted = $baseAlbum->photos->countBy(fn (Photo $photo) => $photo->isVideo() ? 'videos' : 'photos')->all();
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
