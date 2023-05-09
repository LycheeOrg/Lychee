<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbums;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Http\RuleSets\Album\SetAlbumsTitleRuleSet;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * @implements HasAlbums<AbstractAlbum>
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
		/** @var AbstractAlbum $album */
		foreach ($this->albums as $album) {
			if (!Gate::check(AlbumPolicy::CAN_EDIT, $album)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetAlbumsTitleRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albums = $this->albumFactory->findBaseAlbumsOrFail(
			$values[RequestAttribute::ALBUM_IDS_ATTRIBUTE], false
		);
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
	}
}
