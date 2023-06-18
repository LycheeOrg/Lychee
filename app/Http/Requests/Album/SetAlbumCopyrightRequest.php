<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasBaseAlbum;
use App\Contracts\Http\Requests\HasCopyright;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasCopyrightTrait;
use App\Http\RuleSets\Album\SetAlbumCopyrightRuleSet;

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
		return SetAlbumCopyrightRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail(
			$values[RequestAttribute::ALBUM_IDS_ATTRIBUTE], false
		);
		$this->copyright = $values[RequestAttribute::COPYRIGHT_ATTRIBUTE];
	}
}
