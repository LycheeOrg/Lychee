<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasBaseAlbum;
use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Rules\DescriptionRule;
use App\Rules\RandomIDRule;

class SetAlbumDescriptionRequest extends BaseApiRequest implements HasBaseAlbum, HasDescription
{
	use HasBaseAlbumTrait;
	use HasDescriptionTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['present', new DescriptionRule()],
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
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE];
	}
}
