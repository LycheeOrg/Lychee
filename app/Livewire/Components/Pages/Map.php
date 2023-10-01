<?php

namespace App\Livewire\Components\Pages;

use App\Actions\Albums\Top;
use App\Contracts\Livewire\Reloadable;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\MapProviders;
use App\Enum\SmartAlbumType;
use App\Http\Resources\Collections\TopAlbumsResource;
use App\Livewire\DTO\AlbumsFlags;
use App\Livewire\DTO\SessionFlags;
use App\Livewire\Traits\AlbumsPhotosContextMenus;
use App\Livewire\Traits\SilentUpdate;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * This is the "start" page of the gallery
 * Integrate the list of all albums at top level.
 */
class Map extends Component
{
	private TopAlbumsResource $topAlbums;

	#[Locked] public string $title;
	/** @var array<int,string> */
	#[Locked] public array $albumIDs;
	#[Locked] public ?string $albumId = null;
	public AlbumsFlags $flags;
	public SessionFlags $sessionFlags;
	public MapProviders $mapProviders;

	/**
	 * Render component.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		$this->flags = new AlbumsFlags();
		$this->albumIDs = $this->topAlbums->albums->map(fn ($v, $k) => $v->id)->all();
		$this->mapProviders = Configs::getValueAsEnum('map_provider', MapProviders::class);

		return view('livewire.pages.gallery.map');
	}

	public function mount(): void
	{
		$this->sessionFlags = SessionFlags::get();
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
}
