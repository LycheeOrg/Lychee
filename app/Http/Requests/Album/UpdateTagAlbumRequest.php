<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasCopyright;
use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\HasIsAnd;
use App\Contracts\Http\Requests\HasIsPinned;
use App\Contracts\Http\Requests\HasPhotoLayout;
use App\Contracts\Http\Requests\HasPhotoSortingCriterion;
use App\Contracts\Http\Requests\HasTagAlbum;
use App\Contracts\Http\Requests\HasTags;
use App\Contracts\Http\Requests\HasTimelinePhoto;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelinePhotoGranularity;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasCopyrightTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasIsAndTrait;
use App\Http\Requests\Traits\HasIsPinnedTrait;
use App\Http\Requests\Traits\HasPhotoLayoutTrait;
use App\Http\Requests\Traits\HasPhotoSortingCriterionTrait;
use App\Http\Requests\Traits\HasTagAlbumTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Http\Requests\Traits\HasTimelinePhotoTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\TagAlbum;
use App\Rules\CopyrightRule;
use App\Rules\DescriptionRule;
use App\Rules\EnumRequireSupportRule;
use App\Rules\RandomIDRule;
use App\Rules\SlugRule;
use App\Rules\StringRequireSupportRule;
use App\Rules\TitleRule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UpdateTagAlbumRequest extends BaseApiRequest implements HasTagAlbum, HasTitle, HasDescription, HasPhotoSortingCriterion, HasCopyright, HasTags, HasPhotoLayout, HasTimelinePhoto, HasIsPinned, HasIsAnd
{
	use HasTagAlbumTrait;
	use HasTitleTrait;
	use HasDescriptionTrait;
	use HasIsAndTrait;
	use HasPhotoSortingCriterionTrait;
	use HasCopyrightTrait;
	use HasTagsTrait;
	use HasPhotoLayoutTrait;
	use HasTimelinePhotoTrait;
	use HasIsPinnedTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['present', new DescriptionRule()],
			RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE => ['present', 'nullable', new Enum(ColumnSortingPhotoType::class)],
			RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE,
				'nullable', new Enum(OrderSortingType::class),
			],
			RequestAttribute::TAGS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
			RequestAttribute::COPYRIGHT_ATTRIBUTE => ['present', 'nullable', new CopyrightRule()],
			RequestAttribute::IS_PINNED_ATTRIBUTE => ['present', 'boolean'],
			RequestAttribute::ALBUM_PHOTO_LAYOUT => ['present', 'nullable', new Enum(PhotoLayoutType::class)],
			RequestAttribute::ALBUM_TIMELINE_PHOTO => ['present', 'nullable', new Enum(TimelinePhotoGranularity::class), new EnumRequireSupportRule(TimelinePhotoGranularity::class, [TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED], $this->verify())],
			RequestAttribute::IS_AND_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::SLUG_ATTRIBUTE => ['sometimes', 'nullable', new StringRequireSupportRule(null, $this->verify()), new SlugRule($this->input(RequestAttribute::ALBUM_ID_ATTRIBUTE))],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album = $this->album_factory->findBaseAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]
		);

		if (!$album instanceof TagAlbum) {
			throw ValidationException::withMessages([RequestAttribute::ALBUM_ID_ATTRIBUTE => 'album type not supported.']);
		}

		$this->album = $album;
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE];

		$slug = $values[RequestAttribute::SLUG_ATTRIBUTE] ?? null;
		$album->slug = ($slug !== '' ? $slug : null);

		$photo_column = ColumnSortingPhotoType::tryFrom($values[RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE]);
		$photo_order = OrderSortingType::tryFrom($values[RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE]);

		$this->photo_sorting_criterion = $photo_column === null ?
			null :
			new PhotoSortingCriterion($photo_column->toColumnSortingType(), $photo_order);

		$this->photo_layout = PhotoLayoutType::tryFrom($values[RequestAttribute::ALBUM_PHOTO_LAYOUT]);
		$this->photo_timeline = TimelinePhotoGranularity::tryFrom($values[RequestAttribute::ALBUM_TIMELINE_PHOTO]);
		$this->copyright = $values[RequestAttribute::COPYRIGHT_ATTRIBUTE];
		$this->is_pinned = static::toBoolean($values[RequestAttribute::IS_PINNED_ATTRIBUTE]);
		$this->tags = $values[RequestAttribute::TAGS_ATTRIBUTE];
		$this->is_and = static::toBoolean($values[RequestAttribute::IS_AND_ATTRIBUTE]);
	}
}
