<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Add;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * This defines the Import From Dropbox modals.
 * This is the same behaviour as ImportFromUrl but we use a different view to support the dropbox choose.
 */
final class ImportFromDropbox extends ImportFromUrl
{
	/**
	 * Call the parametrized rendering.
	 *
	 * @return View
	 */
	final public function render(): View
	{
		// We reuse the import from server policy.
		// This action reveals the API key of the admin.
		// We do not want to leak that.
		Gate::authorize(AlbumPolicy::CAN_IMPORT_FROM_SERVER, AbstractAlbum::class);

		return view('livewire.forms.add.import-from-dropbox');
	}

	/**
	 * We use a computed property to avoid having this info in the serialized component.
	 *
	 * @return string
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	final public function getApiKeyProperty(): string
	{
		return Configs::getValueAsString('dropbox_key');
	}
}
