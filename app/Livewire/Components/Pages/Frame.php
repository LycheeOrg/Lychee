<?php

declare(strict_types=1);

namespace App\Livewire\Components\Pages;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Exceptions\PhotoCollectionEmptyException;
use App\Exceptions\UnauthorizedException;
use App\Factories\AlbumFactory;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Frame extends Component
{
	private AlbumFactory $albumFactory;
	private PhotoQueryPolicy $photoQueryPolicy;

	public ?string $title = null;
	public ?string $albumId = null;
	public string $frame;
	public string $src = '';
	public string $srcset = '';
	public int $timeout;
	public string $back;

	/**
	 * Render component.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('livewire.pages.gallery.frame');
	}

	public function mount(?string $albumId = null): void
	{
		if (!Configs::getValueAsBool('mod_frame_enabled')) {
			throw new UnauthorizedException();
		}

		$randomAlbumId = Configs::getValueAsString('random_album_id');
		$this->albumId = $albumId ?? (($randomAlbumId !== '') ? $randomAlbumId : null);

		$album = $this->albumId === null ? null : $this->albumFactory->findAbstractAlbumOrFail($this->albumId);
		Gate::authorize(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $album]);

		$this->title = $album?->title;
		$this->loadPhoto();
		$this->timeout = Configs::getValueAsInt('mod_frame_refresh');
		$this->back = $albumId !== null ? route('livewire-gallery-album', ['albumId' => $albumId]) : route('livewire-gallery');
	}

	/**
	 * @param int $retries
	 *
	 * @return array<string,string>
	 *
	 * @throws InternalLycheeException
	 * @throws ModelNotFoundException
	 * @throws InvalidSmartIdException
	 * @throws PhotoCollectionEmptyException
	 * @throws IllegalOrderOfOperationException
	 */
	#[Renderless]
	public function loadPhoto(int $retries = 5): array
	{
		// avoid infinite recursion
		if ($retries === 0) {
			$this->src = '';
			$this->srcset = '';

			return ['src' => '', 'srcset' => ''];
		}

		// default query
		$query = $this->photoQueryPolicy->applySearchabilityFilter(Photo::query()->with(['album', 'size_variants', 'size_variants.sym_links']));

		if ($this->albumId !== null) {
			$query = $this->albumFactory->findAbstractAlbumOrFail($this->albumId)
									 ->photos()
									 ->with(['album', 'size_variants', 'size_variants.sym_links']);
		}

		/** @var ?Photo $photo */
		// PHPStan does not understand that `firstOrFail` returns `Photo`, but assumes that it returns `Model`
		// @phpstan-ignore-next-line
		$photo = $query->inRandomOrder()->first();
		if ($photo === null) {
			$this->title === null ?
				throw new PhotoCollectionEmptyException() : throw new PhotoCollectionEmptyException('Photo collection of ' . $this->title . ' is empty');
		}

		// retry
		if ($photo->isVideo()) {
			return $this->loadPhoto($retries - 1);
		}

		$this->src = $photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()?->url;

		if ($photo->size_variants->getMedium() !== null && $photo->size_variants->getMedium2x() !== null) {
			$this->srcset = $photo->size_variants->getMedium()->url . ' ' . $photo->size_variants->getMedium()->width . 'w';
			$this->srcset .= $photo->size_variants->getMedium2x()->url . ' ' . $photo->size_variants->getMedium2x()->width . 'w';
		} else {
			$this->srcset = '';
		}

		return ['src' => $this->src, 'srcset' => $this->srcset];
	}

	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
		$this->photoQueryPolicy = resolve(PhotoQueryPolicy::class);
	}
}
