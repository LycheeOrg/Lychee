<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasAlbumSortingCriterion;
use App\Contracts\Http\Requests\HasCopyright;
use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\HasLicense;
use App\Contracts\Http\Requests\HasPhotoSortingCriterion;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasAlbumSortingCriterionTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasAspectRatioTrait;
use App\Http\Requests\Traits\HasCopyrightTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Http\Requests\Traits\HasPhotoSortingCriterionTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\Album;
use App\Rules\CopyrightRule;
use App\Rules\DescriptionRule;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UpdateAlbumRequest extends BaseApiRequest implements HasAlbum, HasTitle, HasDescription, HasLicense, HasPhotoSortingCriterion, HasAlbumSortingCriterion, HasCopyright
{
	use HasAlbumTrait;
	use HasLicenseTrait;
	use HasAspectRatioTrait;
	use HasTitleTrait;
	use HasDescriptionTrait;
	use HasPhotoSortingCriterionTrait;
	use HasAlbumSortingCriterionTrait;
	use HasCopyrightTrait;
	use AuthorizeCanEditAlbumTrait;

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
			RequestAttribute::COPYRIGHT_ATTRIBUTE => ['present', 'nullable', new CopyrightRule()],
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
		$this->copyright = $values[RequestAttribute::COPYRIGHT_ATTRIBUTE];
	}
}
