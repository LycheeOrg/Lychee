<?php

namespace App\Livewire\Components\Forms\Album;

use App\Actions\Album\Delete as DeleteAction;
use App\Factories\AlbumFactory;
use App\Http\RuleSets\Album\DeleteAlbumsRuleSet;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class Delete extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	// We need to use an array instead of directly said album id to reuse the rules (because I'm lazy).
	/** @var array<int,string> */
	public array $albumIDs;
	public string $title;

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param BaseAlbum $album to update the attributes of
	 *
	 * @return void
	 */
	public function mount(BaseAlbum $album): void
	{
		$this->albumIDs = [$album->id];
		$this->title = $album->title;
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
	 * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	 */
	public function delete(AlbumFactory $albumFactory): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	{
		$this->validate(DeleteAlbumsRuleSet::rules());

		$baseAlbum = $albumFactory->findBaseAlbumOrFail($this->albumIDs[0], false);

		$this->authorize(AlbumPolicy::CAN_DELETE, $baseAlbum);

		$parent_id = ($baseAlbum instanceof Album) ? $baseAlbum->parent_id : null;

		$delete = resolve(DeleteAction::class);
		$fileDeleter = $delete->do([$baseAlbum->id]);
		App::terminating(fn () => $fileDeleter->do());

		if ($parent_id !== null) {
			return redirect()->to(route('livewire-gallery-album', ['albumId' => $parent_id]));
		}

		return redirect()->to(route('livewire-gallery'));
	}
}
