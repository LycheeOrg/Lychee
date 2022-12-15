<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasTagAlbum;
use App\Contracts\Http\Requests\HasTags;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasTagAlbumTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Models\TagAlbum;
use App\Rules\RandomIDRule;

class SetAlbumTagsRequest extends BaseApiRequest implements HasTagAlbum, HasTags
{
	use HasTagAlbumTrait;
	use HasTagsTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * For historical reasons the parameter of the API is called `show_tags`
	 * and not only `tags`; otherwise `HasTags::TAGS_ATTRIBUTE` could be used.
	 */
	public const SHOW_TAGS_ATTRIBUTE = 'show_tags';

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			self::SHOW_TAGS_ATTRIBUTE => 'required|array|min:1',
			self::SHOW_TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// `findOrFail` returns the union `TagAlbum|Collection<TagAlbum`
		// which is not assignable to `TagAlbum`; but as we query for the ID
		// we never get a collection
		// @phpstan-ignore-next-line
		$this->album = TagAlbum::query()->findOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
		$this->tags = $values[self::SHOW_TAGS_ATTRIBUTE];
	}
}