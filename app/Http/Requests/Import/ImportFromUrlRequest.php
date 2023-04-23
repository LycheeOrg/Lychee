<?php

namespace App\Http\Requests\Import;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\RuleSets\Import\ImportFromUrlRuleSet;
use App\Models\Album;

class ImportFromUrlRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * @var string[]
	 */
	protected array $urls = [];

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return ImportFromUrlRuleSet::rules();
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
		$this->urls = str_replace(' ', '%20', $values[RequestAttribute::URLS_ATTRIBUTE]);
	}

	/**
	 * @return string[]
	 */
	public function urls(): array
	{
		return $this->urls;
	}
}
