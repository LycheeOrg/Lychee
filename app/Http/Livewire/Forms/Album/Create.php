<?php

namespace App\Http\Livewire\Forms\Album;

use App\Facades\Lang;
use App\Http\Livewire\Forms\BaseForm;
use App\Http\Livewire\Traits\InteractWithModal;
use App\Http\RuleSets\AddAlbumRuleSet;

/**
 * Basic form component
 * Livewire forms need to extends from this.
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
		$this->title = Lang::get('ALBUM_NEW_TITLE');
		$this->validate = Lang::get('CREATE_ALBUM');
		$this->cancel = Lang::get('CANCEL');
		// Localization
		$this->formLocale = [
			'title' => 'UNTITLED',
		];
		// values
		$this->form = [
			'title' => '',
			'parentId' => $params['parentId'],
		];
		$this->formHidden = ['parentId'];
	}

	/**
	 * A form has a Submit method.
	 *
	 * @return void
	 */
	public function submit(): void
	{
		/*
		 * Empty error bag
		 */
		$this->resetErrorBag();

		/*
		 * Call Livewire validation on the from
		 */
		$data = $this->validate()['form'];
		dd('die');
	}
}
