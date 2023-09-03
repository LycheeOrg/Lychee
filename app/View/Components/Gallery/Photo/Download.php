<?php

namespace App\View\Components\Gallery\Photo;

use App\Enum\SizeVariantType;
use App\Facades\Helpers;
use App\Http\Resources\Models\SizeVariantResource;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Component;
use Illuminate\View\View;

class Download extends Component
{
	public string $url_placeholder = 'api/Photo::getArchive?photoIDs=%s&kind%s';
	public string $photoId;
	public array $size_variants;

	/**
	 * Mount the photo model and initialize the Component.
	 *
	 * @param Photo $photo
	 *
	 * @return void
	 */
	public function __construct(Photo $photo)
	{
		$this->photoId = $photo->id;

		$medium = $photo->size_variants->getSizeVariant(SizeVariantType::MEDIUM);
		$medium2x = $photo->size_variants->getSizeVariant(SizeVariantType::MEDIUM2X);
		$original = $photo->size_variants->getSizeVariant(SizeVariantType::ORIGINAL);
		$small = $photo->size_variants->getSizeVariant(SizeVariantType::SMALL);
		$small2x = $photo->size_variants->getSizeVariant(SizeVariantType::SMALL2X);
		$thumb = $photo->size_variants->getSizeVariant(SizeVariantType::THUMB);
		$thumb2x = $photo->size_variants->getSizeVariant(SizeVariantType::THUMB2X);

		$downgrade = !Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo]) &&
		!$photo->isVideo() &&
		$photo->size_variants->hasMedium() === true;

		$rq = request();

		$this->size_variants = collect([
			'original' => $original === null ? null : SizeVariantResource::make($original)->noUrl($downgrade)->toArray($rq),
			'medium2x' => $medium2x === null ? null : SizeVariantResource::make($medium2x)->toArray($rq),
			'medium' => $medium === null ? null : SizeVariantResource::make($medium)->toArray($rq),
			'small2x' => $small2x === null ? null : SizeVariantResource::make($small2x)->toArray($rq),
			'small' => $small === null ? null : SizeVariantResource::make($small)->toArray($rq),
			'thumb2x' => $thumb2x === null ? null : SizeVariantResource::make($thumb2x)->toArray($rq),
			'thumb' => $thumb === null ? null : SizeVariantResource::make($thumb)->toArray($rq),
		])
		->filter(fn ($e) => $e !== null)
		->map(function ($v, $k) {
			/** @var array{filesize:int} $v */
			$v['filesize'] = Helpers::getSymbolByQuantity(intval($v['filesize']));

			return $v;
		})
		->all();
	}

	/**
	 * Render the component.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('components.gallery.photo.downloads');
	}
}
