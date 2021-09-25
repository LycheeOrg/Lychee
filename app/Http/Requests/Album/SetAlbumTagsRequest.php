<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasAlbumModelID;
use App\Http\Requests\Contracts\HasTags;
use App\Http\Requests\Traits\HasAlbumModelIDTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Rules\ModelIDRule;
use App\Rules\TagsRule;

class SetAlbumTagsRequest extends BaseApiRequest implements HasAlbumModelID, HasTags
{
	use HasAlbumModelIDTrait;
	use HasTagsTrait;

	/**
	 * For historic reasons the parameter of the API is called `show_tags`
	 * and not only `tags`; otherwise `HasTags::TAGS_ATTRIBUTE` could be used.
	 */
	const SHOW_TAGS_ATTRIBUTE = 'show_tags';

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite([$this->albumID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new ModelIDRule(false)],
			self::SHOW_TAGS_ATTRIBUTE => ['required', new TagsRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = intval($values[HasAlbumID::ALBUM_ID_ATTRIBUTE]) ?? null;
		$this->tags = $values[self::SHOW_TAGS_ATTRIBUTE];
	}
}