<?php

namespace App\Legacy\V1\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\RuleSets\Album\SetAlbumTagRuleSet;
use App\Legacy\V1\Contracts\Http\Requests\HasTagAlbum;
use App\Legacy\V1\Contracts\Http\Requests\HasTags;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasTagAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasTagsTrait;
use App\Models\TagAlbum;

class SetAlbumTagsRequest extends BaseApiRequest implements HasTagAlbum, HasTags
{
	use HasTagAlbumTrait;
	use HasTagsTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetAlbumTagRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $id */
		$id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = TagAlbum::query()->findOrFail($id);
		$this->tags = $values[RequestAttribute::SHOW_TAGS_ATTRIBUTE];
	}
}