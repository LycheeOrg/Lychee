<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasCopyright;
use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\HasPhotoSortingCriterion;
use App\Contracts\Http\Requests\HasTagAlbum;
use App\Contracts\Http\Requests\HasTags;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasCopyrightTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasPhotoSortingCriterionTrait;
use App\Http\Requests\Traits\HasTagAlbumTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\TagAlbum;
use App\Rules\CopyrightRule;
use App\Rules\DescriptionRule;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UpdateTagAlbumRequest extends BaseApiRequest implements HasTagAlbum, HasTitle, HasDescription, HasPhotoSortingCriterion, HasCopyright, HasTags
{
	use HasTagAlbumTrait;
	use HasTitleTrait;
	use HasDescriptionTrait;
	use HasPhotoSortingCriterionTrait;
	use HasCopyrightTrait;
	use HasTagsTrait;
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

		if (!$album instanceof TagAlbum) {
			throw ValidationException::withMessages([RequestAttribute::ALBUM_ID_ATTRIBUTE => 'album type not supported.']);
		}

		$this->album = $album;
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE];

		$photoColumn = ColumnSortingPhotoType::tryFrom($values[RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE]);
		$photoOrder = OrderSortingType::tryFrom($values[RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE]);

		$this->photoSortingCriterion = $photoColumn === null ?
			null :
			new PhotoSortingCriterion($photoColumn->toColumnSortingType(), $photoOrder);

		$this->copyright = $values[RequestAttribute::COPYRIGHT_ATTRIBUTE];
		$this->tags = $values[RequestAttribute::TAGS_ATTRIBUTE];
	}
}
