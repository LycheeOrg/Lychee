<?php

namespace App\Livewire\Components\Pages\Gallery;

use App\Contracts\Livewire\Reloadable;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\QueryBuilderException;
use App\Factories\AlbumFactory;
use App\Livewire\DTO\AlbumFlags;
use App\Livewire\DTO\SessionFlags;
use App\Livewire\Traits\AlbumsPhotosContextMenus;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\SilentUpdate;
use App\Models\Album as ModelsAlbum;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use LycheeOrg\PhpFlickrJustifiedLayout\DTO\Geometry;

/**
 * Album sub module.
 *
 * We just load the layout from config and render.
 * The variable $album is automatically mounted from the Livewire call
 */
class Album extends Component implements Reloadable
{
	use AlbumsPhotosContextMenus;
	use SilentUpdate;
	use Notify;

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
		$this->flags->can_edit = Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]);

		if ($this->flags->is_accessible) {
			$this->num_users = User::count();
			$this->header_url ??= $this->fetchHeaderUrl()?->url;
			$this->num_children = $this->album instanceof ModelsAlbum ? $this->album->children->count() : 0;
			$this->num_photos = $this->album->photos->count();
		}

		return view('livewire.pages.gallery.album');
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
	 * Return the photoIDs (no need to wait to compute the geometry).
	 *
	 * @return array
	 */
	public function getPhotoIDsProperty(): array
	{
		return $this->album->photos->map(fn ($v, $k) => $v->id)->all();
	}

	/**
	 * Used in the JS front-end to manage the selected albums.
	 *
	 * @return array
	 */
	public function getAlbumIDsProperty(): array
	{
		return $this->album instanceof ModelsAlbum ? $this->album->children->map(fn ($v, $k) => $v->id)->all() : [];
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

	public function setCover(?string $photoID): void
	{
		if (!$this->album instanceof ModelsAlbum) {
			return;
		}

		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]);

		$photo = $photoID === null ? null : Photo::query()->findOrFail($photoID);
		if ($photo !== null) {
			Gate::authorize(PhotoPolicy::CAN_SEE, [Photo::class, $photo]);
		}

		$this->album->cover_id = ($this->album->cover_id === $photo->id) ? null : $photo->id;
		$this->album->save();
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}

	/**
	 * Set all photos for given id as starred.
	 *
	 * @param array<int,string> $photoIDs
	 *
	 * @return void
	 */
	public function setStar(array $photoIDs): void
	{
		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $photoIDs]);
		Photo::whereIn('id', $photoIDs)->update(['is_starred' => true]);
	}

	/**
	 * Set all photos for given id as NOT starred.
	 *
	 * @param array $photoIDs
	 *
	 * @return void
	 */
	public function unsetStar(array $photoIDs): void
	{
		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $photoIDs]);
		Photo::whereIn('id', $photoIDs)->update(['is_starred' => false]);
	}
}
