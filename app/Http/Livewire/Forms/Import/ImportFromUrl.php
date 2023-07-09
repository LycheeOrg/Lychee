<?php

namespace App\Http\Livewire\Forms\Import;

use App\Actions\Import\FromUrl;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\UnauthenticatedException;
use App\Http\Livewire\Forms\BaseForm;
use App\Http\Livewire\Traits\InteractWithModal;
use App\Http\RuleSets\Import\ImportFromUrlRuleSet;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * The Album Create MODAL extends directly from BaseForm.
 */
class ImportFromUrl extends BaseForm
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
		return ImportFromUrlRuleSet::rules();
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

		$this->title = __('lychee.UPLOAD_IMPORT_INSTR');
		$this->validate = __('lychee.UPLOAD_IMPORT');
		$this->cancel = __('lychee.CANCEL');
		// Localization
		$this->formLocale = [
			RequestAttribute::URLS_ATTRIBUTE => 'https://',
		];
		// values
		$this->form = [
			RequestAttribute::URLS_ATTRIBUTE => '',
			RequestAttribute::ALBUM_ID_ATTRIBUTE => $params['parentId'],
		];
		$this->formHidden = [RequestAttribute::ALBUM_ID_ATTRIBUTE];
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

		/** @var int $currentUserId */
		$currentUserId = Auth::id() ?? throw new UnauthenticatedException();

		// prepare
		$subject = $this->form[RequestAttribute::URLS_ATTRIBUTE];
		$this->form[RequestAttribute::URLS_ATTRIBUTE] = [$subject];
		$values = $this->validate()['form'];

		$parentAlbumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];

		/** @var Album|null $parentAlbum */
		$parentAlbum = $parentAlbumID === null ? null : Album::query()->findOrFail($parentAlbumID);

		// The replacement below looks suspicious.
		// If it was really necessary, then there would be much more special
		// characters (e.i. for example umlauts in international domain names)
		// which would require replacement by their corresponding %-encoding.
		// However, I assume that the PHP method `fopen` is happily fine with
		// any character and internally handles special characters itself.
		// Hence, either use a proper encoding method here instead of our
		// home-brewed, poor-man replacement or drop it entirely.
		// TODO: Find out what is needed and proceed accordingly.
		$urls = str_replace(' ', '%20', $values[RequestAttribute::URLS_ATTRIBUTE]);

		// Authorize
		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $parentAlbum]);

		// Create
		$fromUrl = resolve(FromUrl::class);
		$fromUrl->do($urls, $parentAlbum, $currentUserId);

		// Do we want refresh or direcly open newly created Album ?
		$this->emitTo('modules.gallery.albums', 'reload');
		$this->emitTo('modules.gallery.album', 'reload');

		$this->close();
	}
}
