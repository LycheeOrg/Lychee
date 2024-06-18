<?php

declare(strict_types=1);

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasTagAlbum;
use App\Contracts\Http\Requests\HasTags;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasTagAlbumTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Http\RuleSets\Album\SetAlbumTagRuleSet;
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