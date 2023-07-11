<?php

namespace App\Http\Livewire\Modules\Gallery;

use App\Enum\Livewire\AlbumMode;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\QueryBuilderException;
use App\Http\Livewire\Components\Base\Openable;
use App\Http\Livewire\Traits\AlbumProperty;
use App\Http\Livewire\Traits\InteractWithModal;
use App\Models\Album as ModelsAlbum;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\SizeVariant;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\View\View;
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

	/**
	 * Because AbstractAlbum is an Interface, it is not possible to make it
	 * and attribute of a Livewire Component as on the "way back" we do not know
	 * in what kind of AbstractAlbum we need to cast it back.
	 *
	 * One way to solve this would actually be to create either an WireableAlbum container
	 * Or to use a computed property on the model. We chose the later.
	 */
	use AlbumProperty;
	public bool $ready_to_load = false;
	public int $width = 0;

	public AlbumMode $layout;

	public ?BaseAlbum $baseAlbum = null;
	public ?BaseSmartAlbum $smartAlbum = null;

	public ?Collection $photos = null;

	public ?string $header_url = null;

	/**
	 * Listeners for roloading the page.
	 *
	 * @var string[]
	 */
	protected $listeners = ['reload', 'open', 'close', 'toggle'];

	/**
	 * Rendering of the blade template.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->layout = Configs::getValueAsEnum('layout', AlbumMode::class);
		$this->header_url ??= $this->fetchHeaderUrl()?->url;

		return view('livewire.modules.gallery.album');
	}

	/**
	 * Method call from the front-end to inform it is time to load the errors.
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
		if ($this->baseAlbum instanceof ModelsAlbum) {
			$this->baseAlbum = ModelsAlbum::findOrFail($this->baseAlbum->id);
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
		return $this->ready_to_load ? $this->getAlbumProperty()->photos : collect([]);
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
		if ($this->getAlbumProperty()->photos->isEmpty()) {
			return null;
		}

		return SizeVariant::query()
			->where('type', '=', SizeVariantType::MEDIUM)
			->whereBelongsTo($this->getAlbumProperty()->photos)
			->where('ratio', '>', 1)
			->inRandomOrder()
			->first();
	}

	public function openSharingModal(): void
	{
		$this->openClosableModal('forms.album.share', __('lychee.CLOSE'));
	}
}
