<?php

namespace App\Livewire\Components\Forms\Album;

use App\Actions\Album\CreateTagAlbum;
use App\Contracts\Models\AbstractAlbum;
use App\Http\RuleSets\Album\AddTagAlbumRuleSet;
use App\Livewire\Traits\InteractWithModal;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The CreateTag Album Modal.
 */
class CreateTag extends Component
{
	/**
	 * Allow modal integration.
	 */
	use InteractWithModal;

	private CreateTagAlbum $create;

	public string $title = '';
	public string $tag = '';
	#[Locked] public array $tags = [];
	public function boot(): void
	{
		$this->create = resolve(CreateTagAlbum::class);
	}

	/**
	 * This defines the set of validation rules to be applied on the input.
	 * It would be a good idea to unify (namely reuse) the rules from the JSON api.
	 *
	 * @return array
	 */
	protected function rules(): array
	{
		return AddTagAlbumRuleSet::rules();
	}

	/**
	 * Mount the component.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function mount(array $params = []): void
	{
		Gate::authorize(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]);
	}

	/**
	 * Call the parametrized rendering.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.add.create-tag');
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

		$this->tags = explode(',', $this->tag);

		// Validate
		$this->validate();

		// Authorize
		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null]);

		// Create
		$new_album = $this->create->create($this->title, $this->tags);

		// Do we want refresh or direcly open newly created Album ?
		$this->close();
		$this->redirect(route('livewire-gallery-album', ['albumId' => $new_album->id]), true);
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
