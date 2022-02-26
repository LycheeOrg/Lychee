<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbums;
use App\Http\Requests\Contracts\HasTitle;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\Extensions\BaseAlbum;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;

/**
 * @implements HasAlbums<BaseAlbum>
 */
class SetAlbumsTitleRequest extends BaseApiRequest implements HasTitle, HasAlbums
{
	use HasTitleTrait;
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumsWrite($this->albums);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbums::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			HasAlbums::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			HasTitle::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albums = $this->albumFactory->findBaseAlbumsOrFail(
			$values[HasAlbums::ALBUM_IDS_ATTRIBUTE], false
		);
		$this->title = $values[HasTitle::TITLE_ATTRIBUTE];
	}
}
