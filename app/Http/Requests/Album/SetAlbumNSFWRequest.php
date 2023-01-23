<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasBaseAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\RuleSets\Album\SetAlbumNSFWRuleSet;

/**
 * Class SetAlbumNSFWRequest.
 */
class SetAlbumNSFWRequest extends BaseApiRequest implements HasBaseAlbum
{
	use HasBaseAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	protected bool $isNSFW = false;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetAlbumNSFWRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]
		);
		$this->isNSFW = static::toBoolean($values[RequestAttribute::IS_NSFW_ATTRIBUTE]);
	}

	public function isNSFW(): bool
	{
		return $this->isNSFW;
	}
}
