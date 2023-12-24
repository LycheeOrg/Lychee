<?php

namespace App\Livewire\Components\Forms\Album;

use App\Contracts\Livewire\Params;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\SmartAlbumType;
use App\Factories\AlbumFactory;
use App\Http\RuleSets\Album\SetAlbumsTitleRuleSet;
use App\Livewire\Components\Pages\Gallery\Album as GalleryAlbum;
use App\Livewire\Traits\InteractWithModal;
use App\Models\BaseAlbumImpl;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Rename extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;

	#[Locked] public ?string $parent_id = null;
	/** @var array<int,string> */
	#[Locked] public array $albumIDs;
	#[Locked] public int $num;
	public string $title = '';

	private AlbumFactory $albumFactory;

	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
	}

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param array{albumID?:string,albumIDs?:array<int,string>,parentID:?string} $params to move
	 *
	 * @return void
	 */
	public function mount(array $params = ['parentID' => null]): void
	{
		$id = $params[Params::ALBUM_ID] ?? null;
		$this->albumIDs = $id !== null ? [$id] : $params[Params::ALBUM_IDS] ?? [];
		$this->num = count($this->albumIDs);

		if ($this->num === 1) {
			$this->title = $this->albumFactory->findBaseAlbumOrFail($this->albumIDs[0], false)->title;
		}

		Gate::authorize(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, $this->albumIDs]);
		$this->parent_id = $params[Params::PARENT_ID] ?? SmartAlbumType::UNSORTED->value;
	}

	/**
	 * Rename.
	 *
	 * @return void
	 */
	public function submit(): void
	{
		$this->validate(SetAlbumsTitleRuleSet::rules());
		Gate::authorize(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, $this->albumIDs]);
		BaseAlbumImpl::query()->whereIn('id', $this->albumIDs)->update(['title' => $this->title]);

		$this->close();
		$this->dispatch('reloadPage')->to(GalleryAlbum::class);
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.rename');
	}

	/**
	 * Add an handle to close the modal form from a user-land call.
	 *
	 * @return void
	 */
	public function close(): void
	{
		$this->closeModal();
	}
}
