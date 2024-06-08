<?php

namespace App\Livewire\Forms;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Models\Album;
use App\Rules\RandomIDRule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class ImportFromUrlForm extends Form
{
	#[Locked]
	public ?string $albumID = null;
	/** @var string[] $urls */
	#[Locked]
	public array $urls = [];

	public string $urlArea = '';

	/**
	 * This allows Livewire to know which values of the $configs we
	 * want to display in the wire:model. Sort of a white listing.
	 *
	 * @return array<string,mixed>
	 */
	protected function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::URLS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::URLS_ATTRIBUTE . '.*' => 'required|string',
		];
	}

	/**
	 * split path into paths array.
	 *
	 * @return void
	 */
	public function prepare()
	{
		$this->urls = array_values(array_filter(explode("\n", $this->urlArea), fn ($elem) => $elem !== ''));

		// The replacement below looks suspicious.
		// If it was really necessary, then there would be much more special
		// characters (e.i. for example umlauts in international domain names)
		// which would require replacement by their corresponding %-encoding.
		// However, I assume that the PHP method `fopen` is happily fine with
		// any character and internally handles special characters itself.
		// Hence, either use a proper encoding method here instead of our
		// home-brewed, poor-man replacement or drop it entirely.
		// TODO: Find out what is needed and proceed accordingly.
		// ? We can't use URL encode because we need to preserve :// and ?
		$this->urls = str_replace(' ', '%20', $this->urls);
	}

	/**
	 * Initialize form data.
	 *
	 * @param ?string $albumID
	 *
	 * @return void
	 */
	public function init(?string $albumID): void
	{
		$this->albumID = $albumID;
	}

	public function getAlbum(): Album|null
	{
		/** @var Album $album */
		$album = $this->albumID === null ? null : Album::query()->findOrFail($this->albumID);

		return $album;
	}
}
