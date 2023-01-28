<?php

namespace App\Http\Livewire\Forms\Album;

use App\Actions\Album\CreateTagAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Livewire\Forms\BaseForm;
use App\Http\Livewire\Traits\InteractWithModal;
use App\Http\RuleSets\Album\AddTagAlbumRuleSet;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * The Tag Album Create MODAL extends directly from BaseForm.
 */
class CreateTag extends BaseForm
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
		parent::mount($params);
		$this->title = __('lychee.ALBUM_NEW_TITLE');
		$this->validate = __('lychee.CREATE_TAG_ALBUM');
		$this->cancel = __('lychee.CANCEL');
		// Localization
		$this->formLocale = [
			RequestAttribute::TITLE_ATTRIBUTE => __('lychee.UNTITLED'),
			RequestAttribute::TAGS_ATTRIBUTE => __('lychee.PHOTO_TAGS'),
		];

		// values
		$this->form = [
			RequestAttribute::TITLE_ATTRIBUTE => '',
			RequestAttribute::TAGS_ATTRIBUTE => '',
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

		// prepare
		// TODO: Refactor tags rules.
		$this->form[RequestAttribute::TAGS_ATTRIBUTE] = explode(',', $this->form[RequestAttribute::TAGS_ATTRIBUTE]);

		// Validate
		$values = $this->validate()['form'];
		$tags = $values[RequestAttribute::TAGS_ATTRIBUTE];
		$title = $values[RequestAttribute::TITLE_ATTRIBUTE];

		// Authorize
		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null]);

		// Create
		resolve(CreateTagAlbum::class)->create($title, $tags);

		// Do we want refresh or direcly open newly created Album ?
		$this->emitTo('modules.gallery.albums', 'reload');
		$this->emitTo('modules.gallery.album', 'reload');

		$this->close();
	}
}
