<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasTagAlbum;
use App\Http\Requests\Contracts\HasTags;
use App\Http\Requests\Traits\HasTagAlbumTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Models\TagAlbum;
use App\Rules\RandomIDRule;

class SetAlbumTagsRequest extends BaseApiRequest implements HasTagAlbum, HasTags
{
	use HasTagAlbumTrait;
	use HasTagsTrait;

	/**
	 * For historical reasons the parameter of the API is called `show_tags`
	 * and not only `tags`; otherwise `HasTags::TAGS_ATTRIBUTE` could be used.
	 */
	public const SHOW_TAGS_ATTRIBUTE = 'show_tags';

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite($this->album);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			self::SHOW_TAGS_ATTRIBUTE => 'required|array|min:1',
			self::SHOW_TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = TagAlbum::query()->findOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]);
		$this->tags = $values[self::SHOW_TAGS_ATTRIBUTE];
	}
}