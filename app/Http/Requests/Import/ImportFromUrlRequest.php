<?php

namespace App\Http\Requests\Import;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Rules\RandomIDRule;

class ImportFromUrlRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;

	public const URL_ATTRIBUTE = 'url';

	/**
	 * @var string[]
	 */
	protected array $urls = [];

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite($this->album);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			self::URL_ATTRIBUTE => 'required|array|min:1',
			self::URL_ATTRIBUTE . '.*' => 'required|string',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$albumID = $values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE];
		$this->album = empty($albumID) ?
			null :
			Album::query()->findOrFail($albumID);
		$this->urls = str_replace(' ', '%20', $values[self::URL_ATTRIBUTE]);
	}

	/**
	 * @return string[]
	 */
	public function urls(): array
	{
		return $this->urls;
	}
}
