<?php

namespace App\Livewire\Forms\Album;

use App\Actions\Album\Create as AlbumCreate;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\UnauthenticatedException;
use App\Http\RuleSets\AddAlbumRuleSet;
use App\Livewire\Forms\BaseForm;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * The Album Create MODAL extends directly from BaseForm.
 */
class Create extends BaseForm
{
	/*
	 * Allow modal integration
	 */
	use InteractWithModal;

	/**
	 * This defines the set of validation rules to be applied on the input.
	 * It would be a good idea to unify (namely reuse) the rules from the JSON api.
	 *
	 * @return array
	 */
	protected function getRuleSet(): array
	{
		return AddAlbumRuleSet::rules();
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
		parent::mount($params);
		$this->title = __('lychee.TITLE_NEW_ALBUM');
		$this->validate = __('lychee.CREATE_ALBUM');
		$this->cancel = __('lychee.CANCEL');
		// Localization
		$this->formLocale = [
			RequestAttribute::TITLE_ATTRIBUTE => __('lychee.UNTITLED'),
		];
		// values
		$this->form = [
			RequestAttribute::TITLE_ATTRIBUTE => '',
			RequestAttribute::PARENT_ID_ATTRIBUTE => $params['parentId'],
		];
		$this->formHidden = [RequestAttribute::PARENT_ID_ATTRIBUTE];
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
		$values = $this->validate()['form'];
		$parentAlbumID = $values[RequestAttribute::PARENT_ID_ATTRIBUTE];
		$title = $values[RequestAttribute::TITLE_ATTRIBUTE];

		/** @var Album|null $parentAlbum */
		$parentAlbum = $parentAlbumID === null ? null : Album::query()->findOrFail($parentAlbumID);

		// Authorize
		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $parentAlbum]);

		/** @var int $ownerId */
		$ownerId = Auth::id() ?? throw new UnauthenticatedException();
		$create = new AlbumCreate($ownerId);
		$create->create($title, $parentAlbum);

		// Do we want refresh or direcly open newly created Album ?
		$this->emitTo('modules.gallery.albums', 'reload');
		$this->emitTo('modules.gallery.album', 'reload');

		$this->close();
	}
}
