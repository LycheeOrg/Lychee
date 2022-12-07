<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasBaseAlbum;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Rules\RandomIDRule;

/**
 * Class SetAlbumNSFWRequest.
 */
class SetAlbumNSFWRequest extends BaseApiRequest implements HasBaseAlbum
{
	use HasBaseAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	public const IS_NSFW_ATTRIBUTE = 'is_nsfw';

	protected bool $isNSFW = false;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			self::IS_NSFW_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]
		);
		$this->isNSFW = static::toBoolean($values[self::IS_NSFW_ATTRIBUTE]);
	}

	public function isNSFW(): bool
	{
		return $this->isNSFW;
	}
}
