<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasTags;
use App\Http\Requests\Contracts\HasTitle;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Rules\TitleRule;

class AddTagAlbumRequest extends BaseApiRequest implements HasTitle, HasTags
{
	use HasTitleTrait;
	use HasTagsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// Sic!
		// Tag albums can only be created below the root album which has the
		// ID `null`.
		return $this->authorizeAlbumWrite(null);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasTitle::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			HasTags::TAGS_ATTRIBUTE => 'required|array|min:1',
			HasTags::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->title = $values[HasTitle::TITLE_ATTRIBUTE];
		$this->tags = $values[HasTags::TAGS_ATTRIBUTE];
	}
}
