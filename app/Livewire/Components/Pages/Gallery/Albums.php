<?php

declare(strict_types=1);

namespace App\Livewire\Components\Pages\Gallery;

use App\Actions\Albums\Top;
use App\Contracts\Livewire\Reloadable;
use App\Contracts\Models\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Http\Resources\Collections\TopAlbumsResource;
use App\Livewire\DTO\AlbumRights;
use App\Livewire\DTO\AlbumsFlags;
use App\Livewire\DTO\SessionFlags;
use App\Livewire\Traits\AlbumsPhotosContextMenus;
use App\Livewire\Traits\SilentUpdate;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * This is the "start" page of the gallery
 * Integrate the list of all albums at top level.
 */
class Albums extends Component implements Reloadable
{
	use AlbumsPhotosContextMenus;
	use SilentUpdate;

	private TopAlbumsResource $topAlbums;

	#[Locked] public string $title;
	#[Locked] public ?string $albumId = null;
	public AlbumsFlags $flags;
	public AlbumRights $rights;
	public SessionFlags $sessionFlags;

	/**
	 * Render component.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		$this->flags = new AlbumsFlags();
		$this->rights = AlbumRights::make(null);
		$this->checkFrameAccess();

		return view('livewire.pages.gallery.albums');
	}

	public function mount(): void
	{
		$this->sessionFlags = SessionFlags::get();
	}

	#[On('reloadPage')]
	public function reloadPage(): void
	{
		$this->topAlbums = resolve(Top::class)->get();
	}

	public function boot(): void
	{
		$this->topAlbums = resolve(Top::class)->get();
		$this->title = Configs::getValueAsString('site_title');
	}

	/**
	 * @return Collection<int,\App\Models\Album>
	 */
	public function getAlbumsProperty(): Collection
	{
		return $this->topAlbums->albums;
	}

	/**
	 * @return Collection<int,\App\SmartAlbums\BaseSmartAlbum|\App\Models\TagAlbum>
	 */
	public function getSmartAlbumsProperty(): Collection
	{
		return $this->topAlbums->smart_albums
			->concat($this->topAlbums->tag_albums);
	}

	/**
	 * @return Collection<int,\App\Models\Album>
	 */
	public function getSharedAlbumsProperty(): Collection
	{
		return $this->topAlbums->shared_albums;
	}

	private function checkFrameAccess(): void
	{
		if ($this->flags->is_mod_frame_enabled !== true) {
			return;
		}

		$randomAlbumId = Configs::getValueAsString('random_album_id');
		$album = resolve(AlbumFactory::class)->findAbstractAlbumOrFail($randomAlbumId);
		if (Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $album]) !== true) {
			$this->flags->is_mod_frame_enabled = false;

			return;
		}

		$this->flags->is_mod_frame_enabled = $album->photos->count() > 0;
	}

	public function getIsLoginLeftProperty(): bool
	{
		return Configs::getValueAsString('login_button_position') === 'left';
	}

	/**
	 * Used in the JS front-end to manage the selected albums.
	 *
	 * @return string[]
	 */
	public function getAlbumIDsProperty(): array
	{
		return $this->topAlbums->albums->map(fn ($v, $k) => $v->id)->all();
	}
}
