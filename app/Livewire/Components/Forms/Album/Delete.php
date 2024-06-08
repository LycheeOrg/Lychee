<?php

namespace App\Livewire\Components\Forms\Album;

use App\Actions\Album\Delete as DeleteAction;
use App\Contracts\Livewire\Params;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\SmartAlbumType;
use App\Factories\AlbumFactory;
use App\Http\RuleSets\Album\DeleteAlbumsRuleSet;
use App\Livewire\Traits\InteractWithModal;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Delete extends Component
{
	use AuthorizesRequests;
	use InteractWithModal;

	// We need to use an array instead of directly said album id to reuse the rules (because I'm lazy).
	/** @var string[] */
	#[Locked] public array $albumIDs;
	#[Locked] public string $parent_id;
	#[Locked] public string $title = '';
	#[Locked] public int $num;
	private AlbumFactory $albumFactory;
	private DeleteAction $deleteAction;

	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
		$this->deleteAction = resolve(DeleteAction::class);
	}

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param array{albumID?:string,albumIDs?:string[],parentID:?string} $params to delete
	 *
	 * @return void
	 */
	public function mount(array $params = ['parentID' => null]): void
	{
		$id = $params[Params::ALBUM_ID] ?? null;
		$this->albumIDs = $id !== null ? [$id] : $params[Params::ALBUM_IDS] ?? [];
		$this->num = count($this->albumIDs);

		if ($this->num === 1) {
			$this->title = $this->albumFactory->findBaseAlbumOrFail($this->albumIDs[0])->title;
		}

		Gate::authorize(AlbumPolicy::CAN_DELETE_ID, [AbstractAlbum::class, $this->albumIDs]);
		$this->parent_id = $params[Params::PARENT_ID] ?? SmartAlbumType::UNSORTED->value;
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.delete');
	}

	/**
	 * Execute deletion.
	 *
	 * @return void
	 */
	public function delete(): void
	{
		$this->validate(DeleteAlbumsRuleSet::rules());

		Gate::authorize(AlbumPolicy::CAN_DELETE_ID, [AbstractAlbum::class, $this->albumIDs]);

		$fileDeleter = $this->deleteAction->do($this->albumIDs);
		App::terminating(fn () => $fileDeleter->do());

		if ($this->parent_id === SmartAlbumType::UNSORTED->value) {
			$this->redirect(route('livewire-gallery'), true);
		} else {
			$this->redirect(route('livewire-gallery-album', ['albumId' => $this->parent_id]), true);
		}
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
