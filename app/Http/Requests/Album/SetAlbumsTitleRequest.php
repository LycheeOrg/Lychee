<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumIDs;
use App\Http\Requests\Contracts\HasTitle;
use App\Http\Requests\Traits\HasAlbumIDsTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Rules\RandomIDListRule;
use App\Rules\TitleRule;

class SetAlbumsTitleRequest extends BaseApiRequest implements HasTitle, HasAlbumIDs
{
	use HasTitleTrait;
	use HasAlbumIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite($this->albumIDs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumIDs::ALBUM_IDS_ATTRIBUTE => ['required', new RandomIDListRule()],
			HasTitle::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumIDs = explode(',', $values[HasAlbumIDs::ALBUM_IDS_ATTRIBUTE]);
		$this->title = $values[HasTitle::TITLE_ATTRIBUTE];
	}
}
