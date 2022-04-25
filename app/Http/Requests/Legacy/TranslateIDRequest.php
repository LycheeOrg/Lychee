<?php

namespace App\Http\Requests\Legacy;

use App\Http\Requests\BaseApiRequest;
use App\Rules\IntegerIDRule;

class TranslateIDRequest extends BaseApiRequest
{
	public const ALBUM_ID_ATTRIBUTE = 'albumID';
	public const PHOTO_ID_ATTRIBUTE = 'photoID';

	protected ?int $albumID = null;
	protected ?int $photoID = null;

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
			self::ALBUM_ID_ATTRIBUTE => [
				'sometimes',
				'required_without:' . self::PHOTO_ID_ATTRIBUTE,
				new IntegerIDRule(false),
			],
			self::PHOTO_ID_ATTRIBUTE => [
				'sometimes',
				'required_without:' . self::ALBUM_ID_ATTRIBUTE,
				new IntegerIDRule(false),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[self::ALBUM_ID_ATTRIBUTE] ?? null;
		$this->photoID = $values[self::PHOTO_ID_ATTRIBUTE] ?? null;
	}

	/**
	 * @return int|null
	 */
	public function albumID(): ?int
	{
		return $this->albumID;
	}

	/**
	 * @return int|null
	 */
	public function photoID(): ?int
	{
		return $this->photoID;
	}
}
