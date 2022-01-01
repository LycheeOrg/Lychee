<?php

namespace App\Http\Requests\Legacy;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Rules\IntegerIDRule;

class TranslateAlbumIDRequest extends BaseApiRequest implements HasAlbumID
{
	use HasAlbumIDTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
	}
}