<?php

namespace App\Livewire\Components\Pages\Gallery;

use App\Enum\ImageOverlayType;
use App\Enum\LicenseType;
use App\Enum\MapProviders;
use App\Http\Resources\Collections\PhotoCollectionResource;
use App\Livewire\DTO\AlbumFlags;
use App\Livewire\DTO\AlbumFormatted;
use App\Livewire\DTO\AlbumRights;
use App\Livewire\DTO\Layouts;
use App\Livewire\DTO\PhotoFlags;
use App\Livewire\DTO\SessionFlags;
use App\Livewire\Traits\AlbumsPhotosContextMenus;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\SilentUpdate;
use App\Livewire\Traits\UsePhotoViewActions;
use App\Models\Album as ModelsAlbum;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Album sub module.
 *
 * We just load the layout from config and render.
 * The variable $album is automatically mounted from the Livewire call
 */
abstract class BaseAlbumComponent extends Component
{
	use AlbumsPhotosContextMenus;
	use SilentUpdate;
	use Notify;

	use UsePhotoViewActions;

	protected Layouts $layouts;

	#[Locked] public ?string $albumId = null;
	#[Locked] public ?string $photoId = null;
	#[Locked] public ?string $header_url = null;
	#[Locked] public int $num_albums = 0;
	#[Locked] public int $num_photos = 0;
	#[Locked] public int $num_users = 0;
	#[Locked] public PhotoFlags $photoFlags;
	#[Locked] public AlbumFlags $flags;
	#[Locked] public AlbumRights $rights;
	public SessionFlags $sessionFlags;

	/**
	 * Rendering of the blade template.
	 *
	 * @return View
	 */
	abstract public function render(): View;

	/**
	 * Return the photoIDs (no need to wait to compute the geometry).
	 *
	 * @return PhotoCollectionResource
	 */
	final public function getPhotosResourceProperty(): PhotoCollectionResource
	{
		return PhotoCollectionResource::make($this->getPhotosProperty());
	}

	/**
	 * Return the photoIDs (no need to wait to compute the geometry).
	 *
	 * @return Collection<int,Photo>|LengthAwarePaginator<Photo>
	 */
	abstract public function getPhotosProperty(): Collection|LengthAwarePaginator;

	/**
	 * Return the albums.
	 *
	 * @return Collection<int,ModelsAlbum>|null
	 */
	abstract public function getAlbumsProperty(): Collection|null;

	/**
	 * Used in the JS front-end to manage the selected albums.
	 *
	 * @return string[]
	 */
	abstract public function getAlbumIDsProperty(): array;

	/**
	 * Back property used to retrieve the URL to step back and back arrow.
	 *
	 * @return string
	 */
	abstract public function getBackProperty(): string;

	/**
	 * Title property for the header.
	 *
	 * @return string
	 */
	abstract public function getTitleProperty(): string;

	/**
	 * Data for Details & Hero.
	 *
	 * @return AlbumFormatted|null
	 */
	abstract public function getAlbumFormattedProperty(): AlbumFormatted|null;

	/**
	 * Return the data used to generate the layout on the front-end.
	 *
	 * @return Layouts
	 */
	final public function getLayoutsProperty(): Layouts
	{
		return $this->layouts;
	}

	/**
	 * Getter for the license types in the front-end.
	 *
	 * @return array<string,string> associated array of license type and their localization
	 */
	final public function getLicensesProperty(): array
	{
		return LicenseType::localized();
	}

	/**
	 * Getter for the OverlayType displayed in photoView.
	 *
	 * @return string enum
	 */
	final public function getOverlayTypeProperty(): string
	{
		return Configs::getValueAsEnum('image_overlay_type', ImageOverlayType::class)->value;
	}

	final public function getMapProviderProperty(): MapProviders
	{
		return Configs::getValueAsEnum('map_provider', MapProviders::class);
	}

	/**
	 * Message displayed when no result or the page is empty.
	 *
	 * @return string
	 */
	abstract public function getNoImagesAlbumsMessageProperty(): string;
}
