<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasDescription;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Rules\DescriptionRule;
use App\Rules\RandomIDRule;

class SetAlbumDescriptionRequest extends BaseApiRequest implements HasAlbumID, HasDescription
{
	use HasAlbumIDTrait;
	use HasDescriptionTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite([$this->albumID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasDescription::DESCRIPTION_ATTRIBUTE => ['required', new DescriptionRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
		$this->description = $values[HasDescription::DESCRIPTION_ATTRIBUTE];
	}
}
