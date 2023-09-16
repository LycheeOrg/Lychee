<?php

namespace App\Livewire\Components\Pages\Gallery;

use App\Contracts\Livewire\Reloadable;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\AlbumLayoutType;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\QueryBuilderException;
use App\Factories\AlbumFactory;
use App\Livewire\Components\Base\ContextMenu;
use App\Livewire\DTO\AlbumFlags;
use App\Livewire\DTO\SessionFlags;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\SilentUpdate;
use App\Models\Album as ModelsAlbum;
use App\Models\Extensions\BaseAlbum;
use App\Models\SizeVariant;
use App\Models\User;
use App\Policies\AlbumPolicy;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use LycheeOrg\PhpFlickrJustifiedLayout\DTO\Geometry;
use LycheeOrg\PhpFlickrJustifiedLayout\LayoutConfig;
use LycheeOrg\PhpFlickrJustifiedLayout\LayoutJustify;

/**
 * Album sub module.
 *
 * We just load the layout from config and render.
 * The variable $album is automatically mounted from the Livewire call
 */
class Album extends Component implements Reloadable
{
	use InteractWithModal;
	use SilentUpdate;

	private AlbumFactory $albumFactory;

	public AlbumFlags $flags;

	public SessionFlags $sessionFlags;

	#[Locked] public string $albumId;
	#[Locked] public int $width = 0;
	public ?AbstractAlbum $album = null;
	#[Locked] public ?string $header_url = null;
	#[Locked] public int $num_children = 0;
	#[Locked] public int $num_photos = 0;
	#[Locked] public int $num_users = 0;
	/**
	 * Boot method, called before any interaction with the component.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
	}

	public function mount(string $albumId): void
	{
		$this->albumId = $albumId;
		$this->flags = new AlbumFlags();
		$this->reloadPage();
	}

	/**
	 * Rendering of the blade template.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->sessionFlags = SessionFlags::get();

		if ($this->flags->is_accessible) {
			$this->num_users = User::count();
			$this->header_url ??= $this->fetchHeaderUrl()?->url;
			$this->num_children = $this->album instanceof ModelsAlbum ? $this->album->children->count() : 0;
			$this->num_photos = $this->album->photos->count();
		}

		return view('livewire.pages.gallery.album');
	}

	/**
	 * Method call from the front-end to inform it is time to load the pictures given the width.
	 *
	 * @return void
	 */
	public function loadAlbum(int $width): void
	{
		$this->flags->is_ready_to_load = true;
		$this->width = $width;
	}

	/**
	 * Reload the data.
	 *
	 * @return void
	 */
	#[On('reloadPage')]
	public function reloadPage(): void
	{
		$this->album = $this->albumFactory->findAbstractAlbumOrFail($this->albumId);
		$this->flags->is_base_album = $this->album instanceof BaseAlbum;
		$this->flags->is_accessible = Gate::check(AlbumPolicy::CAN_ACCESS, [ModelsAlbum::class, $this->album]);

		if (!$this->flags->is_accessible) {
			$this->flags->is_password_protected =
				$this->album->public_permissions() !== null &&
				$this->album->public_permissions()->password !== null;
		}

		if (Auth::check() && !$this->flags->is_accessible && !$this->flags->is_password_protected) {
			$this->redirect(route('livewire-gallery'));
		}
	}

	/**
	 * Album property to support the multiple type.
	 *
	 * @return Geometry|null
	 */
	final public function getGeometryProperty(): ?Geometry
	{
		if ($this->flags->layout !== AlbumLayoutType::JUSTIFIED->value) {
			return null;
		}

		$justify = new LayoutJustify();
		$layoutConfig = new LayoutConfig(
			containerWidth: $this->width,
			containerPadding: 0,
		);

		return $justify->compute($this->getPhotosProperty(), $layoutConfig);
	}

	/**
	 * Computable property to access the photos.
	 * If we are not ready to load, we return an empty array.
	 *
	 * @return Collection
	 */
	public function getPhotosProperty(): Collection
	{
		return $this->flags->is_ready_to_load ? $this->album->photos : collect([]);
	}

	/**
	 * Fetch the header url
	 * TODO: Later this can be also a field from the album and if null we apply the rdm query.
	 *
	 * @return SizeVariant|null
	 *
	 * @throws QueryBuilderException
	 * @throws RelationNotFoundException
	 */
	private function fetchHeaderUrl(): SizeVariant|null
	{
		if ($this->album->photos->isEmpty()) {
			return null;
		}

		return SizeVariant::query()
			->where('type', '=', SizeVariantType::MEDIUM)
			->whereBelongsTo($this->album->photos)
			->where('ratio', '>', 1)
			->inRandomOrder()
			->first();
	}

	public function back(): void
	{
		if ($this->album instanceof ModelsAlbum && $this->album->parent_id !== null) {
			$this->redirect(route('livewire-gallery-album', ['albumId' => $this->album->parent_id]));

			return;
		}

		$this->redirect(route('livewire-gallery'));
	}

	#[Renderless]
	public function openContextMenu(): void
	{
		$this->dispatch('openContextMenu', 'menus.AlbumAdd', ['parentId' => $this->albumId], 'right: 30px; top: 30px; transform-origin: top right;')->to(ContextMenu::class);
	}

	#[Renderless]
	public function openPhotoDropdown(int $x, int $y, string $photoId): void
	{
		$this->dispatch('openContextMenu', 'menus.PhotoDropdown', ['albumId' => $this->albumId, 'photoId' => $photoId], sprintf('transform-origin: top left; left: %dpx; top: %dpx;', $x, $y))->to(ContextMenu::class);
	}

	#[Renderless]
	public function openPhotosDropdown(int $x, int $y, array $photoIds): void
	{
		$this->dispatch('openContextMenu', 'menus.PhotosDropdown', ['albumId' => $this->albumId, 'photoIds' => $photoIds], sprintf('transform-origin: top left; left: %dpx; top: %dpx;', $x, $y))->to(ContextMenu::class);
	}

	#[Renderless]
	public function openAlbumDropdown(int $x, int $y, string $albumID): void
	{
		$this->dispatch('openContextMenu', 'menus.AlbumDropdown', ['parentId' => $this->albumId, 'albumId' => $albumID], sprintf('transform-origin: top left; left: %dpx; top: %dpx;', $x, $y))->to(ContextMenu::class);
	}

	#[Renderless]
	public function openAlbumsDropdown(int $x, int $y, array $albumIds): void
	{
		$this->dispatch('openContextMenu', 'menus.AlbumsDropdown', ['parentId' => $this->albumId, 'albumIds' => $albumIds], sprintf('transform-origin: top left; left: %dpx; top: %dpx;', $x, $y))->to(ContextMenu::class);
	}
}
