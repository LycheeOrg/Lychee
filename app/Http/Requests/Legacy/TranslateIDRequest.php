<?php

namespace App\Http\Requests\Legacy;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Rules\IntegerIDRule;

class TranslateIDRequest extends BaseApiRequest
{
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
			RequestAttribute::ALBUM_ID_ATTRIBUTE => [
				'sometimes',
				'required_without:' . RequestAttribute::PHOTO_ID_ATTRIBUTE,
				new IntegerIDRule(false),
			],
			RequestAttribute::PHOTO_ID_ATTRIBUTE => [
				'sometimes',
				'required_without:' . RequestAttribute::ALBUM_ID_ATTRIBUTE,
				new IntegerIDRule(false),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE] ?? null;
		$this->photoID = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE] ?? null;
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
