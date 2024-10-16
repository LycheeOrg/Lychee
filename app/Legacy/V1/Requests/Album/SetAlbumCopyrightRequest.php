<?php

namespace App\Legacy\V1\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasBaseAlbum;
use App\Legacy\V1\Contracts\Http\Requests\HasCopyright;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasBaseAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasCopyrightTrait;
use App\Rules\CopyrightRule;
use App\Rules\RandomIDRule;

class SetAlbumCopyrightRequest extends BaseApiRequest implements HasBaseAlbum, HasCopyright
{
	use HasBaseAlbumTrait;
	use HasCopyrightTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::COPYRIGHT_ATTRIBUTE => ['required', new CopyrightRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE], false
		);
		$this->copyright = $values[RequestAttribute::COPYRIGHT_ATTRIBUTE];
	}
}
