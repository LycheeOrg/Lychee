<?php

namespace App\Livewire\Components\Forms\Album;

use App\Actions\Album\Create as AlbumCreate;
use App\Contracts\Livewire\Params;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\UnauthenticatedException;
use App\Legacy\V1\RuleSets\AddAlbumRuleSet;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The Create Album Modal.
 */
class Create extends Component
{
	/**
	 * Allow modal integration.
	 */
	use InteractWithModal;

	#[Locked] public ?string $parent_id = null;
	public string $title = '';

	/**
	 * This defines the set of validation rules to be applied on the input.
	 * It would be a good idea to unify (namely reuse) the rules from the JSON api.
	 *
	 * @return array<string,string|array<int,string|\Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Validation\Rules\Enum>>
	 */
	protected function rules(): array
	{
		return AddAlbumRuleSet::rules();
	}

	/**
	 * Mount the component.
	 *
	 * @param array{parentID:string|null} $params
	 *
	 * @return void
	 */
	public function mount(array $params = [Params::PARENT_ID => null]): void
	{
		$this->parent_id = $params[Params::PARENT_ID];
		Gate::authorize(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, [$this->parent_id]]);
	}

	/**
	 * Call the parametrized rendering.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.add.create');
	}

	/**
	 * A form has a Submit method.
	 *
	 * @return void
	 */
	public function submit(): void
	{
		// Reset error bag
		$this->resetErrorBag();

		// Validate
		$this->validate();

		/** @var Album|null $parentAlbum */
		$parentAlbum = $this->parent_id === null ? null : Album::query()->findOrFail($this->parent_id);

		// Authorize
		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $parentAlbum]);

		/** @var int $ownerId */
		$ownerId = Auth::id() ?? throw new UnauthenticatedException();
		$create = new AlbumCreate($ownerId);
		$new_album = $create->create($this->title, $parentAlbum);

		$this->redirect(route('livewire-gallery-album', ['albumId' => $new_album->id]), false);
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
