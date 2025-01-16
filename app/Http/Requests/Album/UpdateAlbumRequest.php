<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasAlbumSortingCriterion;
use App\Contracts\Http\Requests\HasCompactBoolean;
use App\Contracts\Http\Requests\HasCopyright;
use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\HasLicense;
use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\HasPhotoLayout;
use App\Contracts\Http\Requests\HasPhotoSortingCriterion;
use App\Contracts\Http\Requests\HasTimelineAlbum;
use App\Contracts\Http\Requests\HasTimelinePhoto;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumSortingCriterionTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasAspectRatioTrait;
use App\Http\Requests\Traits\HasCompactBooleanTrait;
use App\Http\Requests\Traits\HasCopyrightTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Http\Requests\Traits\HasPhotoLayoutTrait;
use App\Http\Requests\Traits\HasPhotoSortingCriterionTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Http\Requests\Traits\HasTimelineAlbumTrait;
use App\Http\Requests\Traits\HasTimelinePhotoTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Rules\CopyrightRule;
use App\Rules\DescriptionRule;
use App\Rules\EnumRequireSupportRule;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UpdateAlbumRequest extends BaseApiRequest implements HasAlbum, HasTitle, HasDescription, HasLicense, HasPhotoSortingCriterion, HasAlbumSortingCriterion, HasCopyright, HasPhoto, HasCompactBoolean, HasPhotoLayout, HasTimelineAlbum, HasTimelinePhoto
{
	use HasAlbumTrait;
	use HasLicenseTrait;
	use HasAspectRatioTrait;
	use HasTitleTrait;
	use HasPhotoTrait;
	use HasCompactBooleanTrait;
	use HasDescriptionTrait;
	use HasPhotoSortingCriterionTrait;
	use HasAlbumSortingCriterionTrait;
	use HasCopyrightTrait;
	use HasPhotoLayoutTrait;
	use HasTimelineAlbumTrait;
	use HasTimelinePhotoTrait;

	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]) &&
		($this->is_compact ||
		$this->photo === null ||
		$this->photo->album_id === $this->album->id);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new Enum(LicenseType::class)],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['present', new DescriptionRule()],
			RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE => ['present', 'nullable', new Enum(ColumnSortingPhotoType::class)],
			RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE,
				'nullable', new Enum(OrderSortingType::class),
			],
			RequestAttribute::ALBUM_SORTING_COLUMN_ATTRIBUTE => ['present', 'nullable', new Enum(ColumnSortingAlbumType::class)],
			RequestAttribute::ALBUM_SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::ALBUM_SORTING_COLUMN_ATTRIBUTE,
				'nullable', new Enum(OrderSortingType::class),
			],
			RequestAttribute::ALBUM_ASPECT_RATIO_ATTRIBUTE => ['present', 'nullable', new Enum(AspectRatioType::class)],
			RequestAttribute::ALBUM_PHOTO_LAYOUT => ['present', 'nullable', new Enum(PhotoLayoutType::class)],
			RequestAttribute::COPYRIGHT_ATTRIBUTE => ['present', 'nullable', new CopyrightRule()],
			RequestAttribute::IS_COMPACT_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::HEADER_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::ALBUM_TIMELINE_ALBUM => ['present', 'nullable', new Enum(TimelineAlbumGranularity::class), new EnumRequireSupportRule(TimelinePhotoGranularity::class, [TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED], $this->verify)],
			RequestAttribute::ALBUM_TIMELINE_PHOTO => ['present', 'nullable', new Enum(TimelinePhotoGranularity::class), new EnumRequireSupportRule(TimelinePhotoGranularity::class, [TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED], $this->verify)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album = $this->albumFactory->findBaseAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]
		);

		if (!$album instanceof Album) {
			throw ValidationException::withMessages([RequestAttribute::ALBUM_ID_ATTRIBUTE => 'album type not supported.']);
		}

		$this->album = $album;
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE];
		$this->license = LicenseType::tryFrom($values[RequestAttribute::LICENSE_ATTRIBUTE]);

		$photoColumn = ColumnSortingPhotoType::tryFrom($values[RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE]);
		$photoOrder = OrderSortingType::tryFrom($values[RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE]);

		$this->photoSortingCriterion = $photoColumn === null ?
			null :
			new PhotoSortingCriterion($photoColumn->toColumnSortingType(), $photoOrder);

		$albumColumn = ColumnSortingPhotoType::tryFrom($values[RequestAttribute::ALBUM_SORTING_COLUMN_ATTRIBUTE]);
		$albumOrder = OrderSortingType::tryFrom($values[RequestAttribute::ALBUM_SORTING_ORDER_ATTRIBUTE]);

		$this->albumSortingCriterion = $albumColumn === null ?
			null :
			new AlbumSortingCriterion($albumColumn->toColumnSortingType(), $albumOrder);

		$this->aspectRatio = AspectRatioType::tryFrom($values[RequestAttribute::ALBUM_ASPECT_RATIO_ATTRIBUTE]);
		$this->photoLayout = PhotoLayoutType::tryFrom($values[RequestAttribute::ALBUM_PHOTO_LAYOUT]);
		$this->album_timeline = TimelineAlbumGranularity::tryFrom($values[RequestAttribute::ALBUM_TIMELINE_ALBUM]);
		$this->photo_timeline = TimelinePhotoGranularity::tryFrom($values[RequestAttribute::ALBUM_TIMELINE_PHOTO]);

		$this->copyright = $values[RequestAttribute::COPYRIGHT_ATTRIBUTE];

		$this->is_compact = static::toBoolean($values[RequestAttribute::IS_COMPACT_ATTRIBUTE]);

		if ($this->is_compact) {
			return;
		}

		/** @var string|null $photoId */
		$photoId = $values[RequestAttribute::HEADER_ID_ATTRIBUTE];
		$this->photo = $photoId !== null ? Photo::query()->findOrFail($photoId) : null;
	}
}
