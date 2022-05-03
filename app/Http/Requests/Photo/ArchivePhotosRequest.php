<?php

namespace App\Http\Requests\Photo;

use App\Actions\Photo\Archive;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotos;
use App\Http\Requests\Contracts\HasSizeVariant;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\Requests\Traits\HasSizeVariantTrait;
use App\Models\Photo;
use App\Rules\RandomIDListRule;
use App\Rules\SizeVariantRule;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArchivePhotosRequest extends BaseApiRequest implements HasPhotos, HasSizeVariant
{
	use HasPhotosTrait;
	use HasSizeVariantTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotosDownload($this->photos);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotos::PHOTO_IDS_ATTRIBUTE => ['required', new RandomIDListRule()],
			HasSizeVariant::SIZE_VARIANT_ATTRIBUTE => ['required', new SizeVariantRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->sizeVariant = $values[HasSizeVariant::SIZE_VARIANT_ATTRIBUTE];

		$photoQuery = Photo::with(['album']);
		// The condition is required, because Lychee also supports to archive
		// the "live video" as a size variant which is not a proper size variant
		if (array_key_exists($this->sizeVariant, Archive::VARIANT2VARIANT)) {
			// If a proper size variant is requested, eagerly load the size
			// variants but only the requested type due to efficiency reasons
			$photoQuery = $photoQuery->with([
				'size_variants' => function (HasMany $r) {
					// The ridiculous mapping `VARIANT2VARIANT` is only
					// necessary, because the size variant with the largest
					// dimensions is called `FULL` by the front-end in the
					// context of archiving, but `ORIGINAL` everywhere else.
					// This should be made consistent.
					// Although, `ORIGINAL` is used more prominently, `FULL`
					// is probably the better wording.
					// TODO: Fix this and make it consistent.
					$r->where('type', '=', Archive::VARIANT2VARIANT[$this->sizeVariant]);
				},
			]);
		}
		$this->photos = $photoQuery->findOrFail(
			explode(',', $values[HasPhotos::PHOTO_IDS_ATTRIBUTE])
		);
	}
}
