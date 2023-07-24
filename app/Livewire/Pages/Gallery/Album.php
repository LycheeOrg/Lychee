<?php

namespace App\Livewire\Pages\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\Livewire\AlbumMode;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\QueryBuilderException;
use App\Factories\AlbumFactory;
use App\Livewire\Components\Base\Openable;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Album as ModelsAlbum;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use LycheeOrg\PhpFlickrJustifiedLayout\DTO\Geometry;
use LycheeOrg\PhpFlickrJustifiedLayout\LayoutConfig;
use LycheeOrg\PhpFlickrJustifiedLayout\LayoutJustify;

/**
 * Album sub module.
 *
 * We just load the layout from config and render.
 * The variable $album is automatically mounted from the Livewire call
 */
class Album extends Openable
{
	use InteractWithModal;

	private AlbumFactory $albumFactory;

	#[Locked]
	public string $albumId;

	/**
	 * Because AbstractAlbum is an Interface, it is not possible to make it
	 * and attribute of a Livewire Component as on the "way back" we do not know
	 * in what kind of AbstractAlbum we need to cast it back.
	 *
	 * One way to solve this would actually be to create either an WireableAlbum container
	 * Or to use a computed property on the model. We chose the later.
	 */
	#[Locked]
	public bool $locked = false;

	#[Locked]
	public bool $ready_to_load = false;

	public bool $is_base_album = false;

	public int $width = 0;

	public AlbumMode $layout;

	public ?AbstractAlbum $album = null;

	public ?string $header_url = null;

	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
	}

	public function mount(string $albumId): void
	{
		$this->albumId = $albumId;
		$this->album = $this->albumFactory->findAbstractAlbumOrFail($this->albumId);
		$this->is_base_album = $this->album instanceof BaseAlbum;
		// $this->locked = Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * Rendering of the blade template.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->layout = Configs::getValueAsEnum('layout', AlbumMode::class);
		$this->header_url ??= $this->fetchHeaderUrl()?->url;

		return view('livewire.pages.gallery.album');
	}

	/**
	 * Method call from the front-end to inform it is time to load the pictures given the width.
	 *
	 * @return void
	 */
	public function loadAlbum(int $width): void
	{
		$this->ready_to_load = true;
		$this->width = $width;
	}

	/**
	 * Reload the data.
	 *
	 * @return void
	 */
	public function reload(): void
	{
		if ($this->album instanceof ModelsAlbum) {
			$this->album = $this->albumFactory->findBaseAlbumOrFail($this->album->id);
		}
	}

	/**
	 * Album property to support the multiple type.
	 *
	 * @return Geometry|null
	 */
	final public function getGeometryProperty(): ?Geometry
	{
		if ($this->layout !== AlbumMode::JUSTIFIED) {
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
		return $this->ready_to_load ? $this->album->photos : collect([]);
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

	public function openSharingModal(): void
	{
		$this->openClosableModal('forms.album.share', __('lychee.CLOSE'));
	}

	public function back() {
		if ($this->album instanceof ModelsAlbum && $this->album->parent_id !== null) {
			return $this->redirect(route('livewire-gallery-album', ['albumId' => $this->album->parent_id]));
		}

		return $this->redirect(route('livewire-gallery'));
	}
}
