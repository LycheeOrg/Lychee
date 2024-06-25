<?php

namespace App\Livewire\Components\Pages\Gallery;

use App\Contracts\Livewire\Reloadable;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\AspectRatioType;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\QueryBuilderException;
use App\Factories\AlbumFactory;
use App\Livewire\Components\Forms\Album\SetHeader;
use App\Livewire\DTO\AlbumFlags;
use App\Livewire\DTO\AlbumFormatted;
use App\Livewire\DTO\AlbumRights;
use App\Livewire\DTO\Layouts;
use App\Livewire\DTO\PhotoFlags;
use App\Livewire\DTO\ProtectedCollection;
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
	private AlbumFormatted $formatted;
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
		$this->albumsCollection = new ProtectedCollection(type: 'album');
		$this->photosCollection = new ProtectedCollection(type: 'photo');
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
		$this->num_albums = 0;
		$this->num_albums = 0;

		if ($this->flags->is_accessible) {
			$this->formatted = new AlbumFormatted($this->album, $this->fetchHeaderUrl()?->url);
			$this->num_users = User::count();

			$this->photosCollection->set($this->album->photos);
			$this->num_photos = $this->photosCollection->get()->count();

			// No photos, no frame
			if ($this->num_photos === 0) {
				$this->flags->is_mod_frame_enabled = false;
			}

			if ($this->album instanceof ModelsAlbum) {
				$this->albumsCollection->set($this->album->children()->getResults());
				$this->num_albums = $this->albumsCollection->get()->count();
			} else {
				$this->albumsCollection->set(null);
			}

			$is_latitude_longitude_found = false;
			if ($this->album instanceof ModelsAlbum) {
				$is_latitude_longitude_found = $this->album->all_photos()->whereNotNull('latitude')->whereNotNull('longitude')->count() > 0;
				$aspectRatio = $this->album->album_thumb_aspect_ratio ?? Configs::getValueAsEnum('default_album_thumb_aspect_ratio', AspectRatioType::class);
				$this->flags->album_thumb_css_aspect_ratio = $aspectRatio->css();
				$this->flags->cover_id = $this->album->cover_id;
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
		} else {
			$this->albumsCollection->set(null);
			$this->photosCollection->set(collect([]));
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
		$this->flags->is_search_accessible = $this->flags->is_search_accessible && $this->album instanceof ModelsAlbum;
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
	 * Fetch the header url.
	 *
	 * @return SizeVariant|null
	 *
	 * @throws QueryBuilderException
	 * @throws RelationNotFoundException
	 */
	private function fetchHeaderUrl(): SizeVariant|null
	{
		$headerSizeVariant = null;

		if (Configs::getValueAsBool('use_album_compact_header')) {
			return null;
		}

		if ($this->album instanceof ModelsAlbum && $this->album->header_id === SetHeader::COMPACT_HEADER) {
			return null;
		}

		if ($this->album->photos->isEmpty()) {
			return null;
		}

		if ($this->album instanceof ModelsAlbum && $this->album->header_id !== null) {
			$headerSizeVariant = SizeVariant::query()
				->where('photo_id', '=', $this->album->header_id)
				->whereIn('type', [SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL])
				->orderBy('type', 'asc')
				->first();
		}

		if ($headerSizeVariant !== null) {
			return $headerSizeVariant;
		}

		$query_ratio = SizeVariant::query()
					->select('photo_id')
					->whereBelongsTo($this->album->photos)
					->where('ratio', '>', 1) // ! we prefer landscape first.
					->whereIn('type', [SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL]);
		$num = $query_ratio->count() - 1;
		$photo = $query_ratio->skip(rand(0, $num))->first();

		if ($photo === null) {
			$query = SizeVariant::query()
				->select('photo_id')
				->whereBelongsTo($this->album->photos)
				->whereIn('type', [SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL]);
			$num = $query->count() - 1;
			$photo = $query->skip(rand(0, $num))->first();
		}

		return $photo === null ? null : SizeVariant::query()
			->where('photo_id', '=', $photo->photo_id)
			->where('type', '>', 1)
			->orderBy('type', 'asc')
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
		return $this->formatted;
	}

	public function getNoImagesAlbumsMessageProperty(): string
	{
		return 'Nothing to see here';
	}
}
