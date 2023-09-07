<?php

namespace App\Livewire\Components\Pages\Gallery;

use App\Actions\Albums\Top;
use App\Contracts\Livewire\Reloadable;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\SmartAlbumType;
use App\Http\Resources\Collections\TopAlbumsResource;
use App\Livewire\Components\Base\ContextMenu;
use App\Livewire\DTO\SessionFlags;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

/**
 * This is the "start" page of the gallery
 * Integrate the list of all albums at top level.
 */
class Albums extends Component implements Reloadable
{
	use InteractWithModal;

	private TopAlbumsResource $topAlbums;

	public SessionFlags $sessionFlags;

	public string $title;

	public bool $can_use_2fa;

	/**
	 * Render component.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('livewire.pages.gallery.albums');
	}

	public function mount(): void
	{
		$this->sessionFlags = SessionFlags::get();
		$this->can_use_2fa = !Auth::check() && (WebAuthnCredential::query()->whereNull('disabled_at')->count() > 0);
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

	public function getAlbumsProperty(): Collection
	{
		return $this->topAlbums->albums;
	}

	public function getSmartAlbumsProperty(): Collection
	{
		return $this->topAlbums->smart_albums
			// We filter out the public one (we don't remove it completely to not break the other front-end).
			->filter(fn (AbstractAlbum $e, $k) => $e->id !== SmartAlbumType::PUBLIC->value)
			->concat($this->topAlbums->tag_albums)
			->reject(fn ($album) => $album === null);
	}

	public function getSharedAlbumsProperty(): Collection
	{
		return $this->topAlbums->shared_albums;
	}

	/**
	 * When no albums are present we simply open the login modal.
	 * [Renderless] indicates that we do not need to call render() on this component.
	 *
	 * @return void
	 */
	// #[Renderless]
	// public function openLoginModal(): void
	// {
	// 	$this->openModal('modals.login');
	// }

	/**
	 * Open the context menu.
	 * [Renderless] indicates that we do not need to call render() on this component.
	 *
	 * @return void
	 */
	#[Renderless]
	public function openContextMenu(): void
	{
		$this->dispatch('openContextMenu', 'menus.AlbumAdd', ['parentId' => null])->to(ContextMenu::class);
	}

	#[Renderless]
	public function emptyRequest(): void
	{
	}
}
