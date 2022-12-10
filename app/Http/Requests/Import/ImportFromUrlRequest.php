<?php

namespace App\Http\Requests\Import;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Rules\RandomIDRule;

class ImportFromUrlRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	public const URLS_ATTRIBUTE = 'urls';

	/**
	 * @var string[]
	 */
	protected array $urls = [];

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			self::URLS_ATTRIBUTE => 'required|array|min:1',
			self::URLS_ATTRIBUTE . '.*' => 'required|string',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$albumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $albumID === null ?
			null :
			Album::query()->findOrFail($albumID);
		// The replacement below looks suspicious.
		// If it was really necessary, then there would be much more special
		// characters (e.i. for example umlauts in international domain names)
		// which would require replacement by their corresponding %-encoding.
		// However, I assume that the PHP method `fopen` is happily fine with
		// any character and internally handles special characters itself.
		// Hence, either use a proper encoding method here instead of our
		// home-brewed, poor-man replacement or drop it entirely.
		// TODO: Find out what is needed and proceed accordingly.
		$this->urls = str_replace(' ', '%20', $values[self::URLS_ATTRIBUTE]);
	}

	/**
	 * @return string[]
	 */
	public function urls(): array
	{
		return $this->urls;
	}
}
