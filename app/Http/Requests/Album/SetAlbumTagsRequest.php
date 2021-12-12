<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasTags;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Rules\RandomIDRule;
use App\Rules\TagsRule;

class SetAlbumTagsRequest extends BaseApiRequest implements HasAlbumID, HasTags
{
	use HasAlbumIDTrait;
	use HasTagsTrait;

	/**
	 * For historic reasons the parameter of the API is called `show_tags`
	 * and not only `tags`; otherwise `HasTags::TAGS_ATTRIBUTE` could be used.
	 */
	public const SHOW_TAGS_ATTRIBUTE = 'show_tags';

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
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			self::SHOW_TAGS_ATTRIBUTE => ['required', new TagsRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
		$this->tags = $values[self::SHOW_TAGS_ATTRIBUTE];
	}
}