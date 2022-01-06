<?php

namespace App\Http\Requests\Legacy;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasPhotoID;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Http\Requests\Traits\HasPhotoIDTrait;
use App\Rules\IntegerIDRule;

class TranslateIDRequest extends BaseApiRequest implements HasAlbumID, HasPhotoID
{
	use HasAlbumIDTrait;
	use HasPhotoIDTrait;

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
			HasAlbumID::ALBUM_ID_ATTRIBUTE => [
				'sometimes',
				'required_without:' . HasPhotoID::PHOTO_ID_ATTRIBUTE,
				new IntegerIDRule(false),
			],
			HasPhotoID::PHOTO_ID_ATTRIBUTE => [
				'sometimes',
				'required_without:' . HasAlbumID::ALBUM_ID_ATTRIBUTE,
				new IntegerIDRule(false),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE] ?? null;
		$this->photoID = $values[HasPhotoID::PHOTO_ID_ATTRIBUTE] ?? null;
	}
}