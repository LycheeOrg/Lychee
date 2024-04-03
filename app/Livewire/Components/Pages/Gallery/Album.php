<?php

namespace App\Livewire\Components\Pages\Gallery;

use App\Contracts\Livewire\Reloadable;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\AspectRatioType;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\QueryBuilderException;
use App\Factories\AlbumFactory;
use App\Livewire\DTO\AlbumFlags;
use App\Livewire\DTO\AlbumFormatted;
use App\Livewire\DTO\AlbumRights;
use App\Livewire\DTO\Layouts;
use App\Livewire\DTO\PhotoFlags;
use App\Livewire\DTO\SessionFlags;
use App\Models\Album as ModelsAlbum;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;

/**
 * Album sub module.
 *
 * We just load the layout from config and render.
 * The variable $album is automatically mounted from the Livewire call
 */
class Album extends BaseAlbumComponent implements Reloadable
{
	private AlbumFactory $albumFactory;

	public ?AbstractAlbum $album = null;

	/**
	 * Boot method, called before any interaction with the component.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
		$this->layouts = new Layouts();
	}

	public function mount(string $albumId, string $photoId = ''): void
	{
		$this->albumId = $albumId;
		$this->photoId = $photoId;
		$this->flags = new AlbumFlags();

		$this->reloadPage();
	}

	/**
	 * Rendering of the blade template.
	 *
	 * @return View
	 */
	final public function render(): View
	{
		$this->sessionFlags = SessionFlags::get();
		$this->rights = AlbumRights::make($this->album);

		if ($this->flags->is_accessible) {
			$this->num_users = User::count();
			$this->header_url ??= $this->fetchHeaderUrl()?->url;
			$this->num_albums = $this->album instanceof ModelsAlbum ? $this->album->children->count() : 0;
			$this->num_photos = $this->album->photos->count();

			// No photos, no frame
			if ($this->num_photos === 0) {
				$this->flags->is_mod_frame_enabled = false;
			}

			$is_latitude_longitude_found = false;
			if ($this->album instanceof ModelsAlbum) {
				$is_latitude_longitude_found = $this->album->all_photos()->whereNotNull('latitude')->whereNotNull('longitude')->count() > 0;
				$aspectRatio = $this->album->album_thumb_aspect_ratio ?? Configs::getValueAsEnum('default_album_thumb_aspect_ratio', AspectRatioType::class);
				$this->flags->album_thumb_css_aspect_ratio = $aspectRatio->css();
			} else {
				$is_latitude_longitude_found = $this->album->photos()->whereNotNull('latitude')->whereNotNull('longitude')->count() > 0;
			}
			// Only display if there are actual data
			$this->flags->is_map_accessible = $this->flags->is_map_accessible && $is_latitude_longitude_found;

			$this->photoFlags = new PhotoFlags(
				can_autoplay: true,
				can_rotate: Configs::getValueAsBool('editor_enabled'),
				can_edit: $this->rights->can_edit,
			);
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
	 * @return Collection<Photo>
	 */
	public function getPhotosProperty(): Collection
	{
		return $this->album->photos;
	}

	/**
	 * @return Collection<ModelsAlbum>|null
	 */
	public function getAlbumsProperty(): Collection|null
	{
		if ($this->album instanceof ModelsAlbum) {
			/** @var Collection<ModelsAlbum> $res */
			$res = $this->album->children()->getResults();

			return $res;
		}

		return null;
	}

	/**
	 * Used in the JS front-end to manage the selected albums.
	 *
	 * @return array
	 */
	public function getAlbumIDsProperty(): array
	{
		return $this->getAlbumsProperty()?->map(fn ($v, $k) => $v->id)?->all() ?? [];
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
		if (Configs::getValueAsBool('use_album_compact_header')) {
			return null;
		}

		if ($this->album->photos->isEmpty()) {
			return null;
		}

		if (!$this->album instanceof ModelsAlbum || !isset($this->album->header_id)) {
			return $this->fetchRandomHeaderUrl();
		}

		$medium = SizeVariant::query()
			->where('type', '=', SizeVariantType::MEDIUM)
			->where('photo_id', '=', $this->album->header_id)
			->first();

		if ($medium !== null) {
			return $medium;
		}

		return SizeVariant::query()
			->where('type', '=', SizeVariantType::SMALL2X)
			->where('photo_id', '=', $this->album->header_id)
			->first();
	}

	protected function fetchRandomHeaderUrl(): SizeVariant|null
	{
		$medium = SizeVariant::query()
			->where('type', '=', SizeVariantType::MEDIUM)
			->whereBelongsTo($this->album->photos)
			->where('ratio', '>', 1)
			->inRandomOrder()
			->first();

		if ($medium !== null) {
			return $medium;
		}

		return SizeVariant::query()
			->where('type', '=', SizeVariantType::SMALL2X)
			->whereBelongsTo($this->album->photos)
			->where('ratio', '>', 1)
			->inRandomOrder()
			->first();
	}

	public function getBackProperty(): string
	{
		if ($this->album instanceof ModelsAlbum && $this->album->parent_id !== null) {
			return route('livewire-gallery-album', ['albumId' => $this->album->parent_id]);
		}

		return route('livewire-gallery');
	}

	public function getTitleProperty(): string
	{
		return $this->album->title;
	}

	#[Renderless]
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

	#[Renderless]
	#[On('setAsCover')]
	public function setAsCover(string $albumID): void
	{
		if (!$this->album instanceof ModelsAlbum) {
			return;
		}

		Gate::authorize(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, $this->album]);
		// We are freezing this cover to the album and to the child.

		/** @var ModelsAlbum $child */
		$child = $this->albumFactory->findAbstractAlbumOrFail($albumID);

		$this->album->cover_id = ($this->album->cover_id === $child->thumb->id) ? null : $child->thumb->id;
		$this->album->save();
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}

	public function getAlbumFormattedProperty(): AlbumFormatted
	{
		return new AlbumFormatted($this->album, $this->fetchHeaderUrl()?->url);
	}

	public function getNoImagesAlbumsMessageProperty(): string
	{
		return 'Nothing to see here';
	}
}
