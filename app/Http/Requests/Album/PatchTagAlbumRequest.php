<?php

namespace App\Http\Requests\Album;

use App\DTO\PhotoSortingCriterion;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbums;
use App\Http\Requests\Contracts\HasDescription;
use App\Http\Requests\Contracts\HasSortingCriterion;
use App\Http\Requests\Contracts\HasTags;
use App\Http\Requests\Contracts\HasTitle;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasSortingCriterionTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\TagAlbum;
use App\Rules\DescriptionRule;
use App\Rules\OrderRule;
use App\Rules\PhotoSortingRule;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;

class PatchTagAlbumRequest extends BaseApiRequest implements HasAlbums, HasDescription, HasTitle, HasSortingCriterion, HasTags
{
	use HasAlbumsTrait;
	use HasDescriptionTrait;
	use HasTitleTrait;
	use HasSortingCriterionTrait;
	use HasTagsTrait;

	public const IS_NSFW_ATTRIBUTE = 'is_nsfw';

	protected ?bool $isNSFW = false;

	protected bool $hasSorting = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumsWrite($this->albums());
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbums::ALBUM_IDS_ATTRIBUTE => ['required', new RandomIDRule(false)],
			self::IS_NSFW_ATTRIBUTE => 'boolean',
			HasDescription::DESCRIPTION_ATTRIBUTE => [new DescriptionRule()],
			HasTitle::TITLE_ATTRIBUTE => [new TitleRule()],
			HasSortingCriterion::SORTING_COLUMN_ATTRIBUTE => [new PhotoSortingRule()],
			HasSortingCriterion::SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . HasSortingCriterion::SORTING_COLUMN_ATTRIBUTE,
				new OrderRule(true),
			],
			HasTags::TAGS_ATTRIBUTE => 'array|min:1',
			HasTags::TAGS_ATTRIBUTE . '.*' => 'string|min:1',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albums = TagAlbum::query()->findOrFail(explode(',', $values[HasAlbums::ALBUM_IDS_ATTRIBUTE]));
		$this->isNSFW = array_key_exists(self::IS_NSFW_ATTRIBUTE, $values) ? static::toBoolean($values[self::IS_NSFW_ATTRIBUTE]) : null;
		$this->description = array_key_exists(HasDescription::DESCRIPTION_ATTRIBUTE, $values) ? static::toBoolean($values[HasDescription::DESCRIPTION_ATTRIBUTE]) : null;
		$this->title = array_key_exists(HasTitle::TITLE_ATTRIBUTE, $values) ? $values[HasTitle::TITLE_ATTRIBUTE] : null;
		$this->hasSorting = array_key_exists(HasSortingCriterion::SORTING_COLUMN_ATTRIBUTE, $values);
		if ($this->hasSorting) {
			$column = $values[HasSortingCriterion::SORTING_COLUMN_ATTRIBUTE];
			$this->sortingCriterion = empty($column) ?
				null :
				new PhotoSortingCriterion($column, $values[HasSortingCriterion::SORTING_ORDER_ATTRIBUTE]);
		}
		$this->tags = array_key_exists(HasTags::TAGS_ATTRIBUTE, $values) ? $values[HasTags::TAGS_ATTRIBUTE] : null;
	}

	public function isNSFW(): ?bool
	{
		return $this->isNSFW;
	}

	public function hasSorting(): bool
	{
		return $this->hasSorting;
	}
}
