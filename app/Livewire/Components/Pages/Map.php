<?php

namespace App\Livewire\Components\Pages;

use App\Actions\Album\PositionData as AlbumPositionData;
use App\Actions\Albums\PositionData as RootPositionData;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\MapProviders;
use App\Factories\AlbumFactory;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Map extends Component
{
	private RootPositionData $rootPositionData;
	private AlbumPositionData $albumPositionData;
	private AlbumFactory $albumFactory;
	private ?AbstractAlbum $album = null;

	#[Locked] public string $title;
	#[Locked] public ?string $albumId = null;
	public MapProviders $map_provider;

	/**
	 * Render component.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		$this->map_provider = Configs::getValueAsEnum('map_provider', MapProviders::class);

		return view('livewire.pages.gallery.map');
	}

	public function mount(?string $albumId = null): void
	{
		$this->albumId = $albumId;

		if ($albumId !== null) {
			$this->album = $this->albumFactory->findAbstractAlbumOrFail($this->albumId);
			$this->title = $this->album->title;
		}

		Gate::authorize(AlbumPolicy::CAN_ACCESS_MAP, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * @return array
	 */
	public function getDataProperty(): array
	{
		if ($this->albumId === null) {
			/** @var array $ret */
			$ret = $this->rootPositionData->do()->toArray(request());

			return $ret;
		}

		$includeSubAlbums = Configs::getValueAsBool('map_include_subalbums');

		/** @var array $ret */
		$ret = $this->albumPositionData->get($this->album, $includeSubAlbums)->toArray(request());

		return $ret;
	}

	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
		$this->rootPositionData = resolve(RootPositionData::class);
		$this->albumPositionData = resolve(AlbumPositionData::class);
		$this->title = Configs::getValueAsString('site_title');
	}

	public function back(): mixed
	{
		if ($this->albumId === null) {
			return $this->redirect(route('livewire-gallery'), true);
		} else {
			return $this->redirect(route('livewire-gallery-album', ['albumId' => $this->albumId]), true);
		}
	}
}
