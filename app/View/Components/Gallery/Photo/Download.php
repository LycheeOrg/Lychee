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
	/** @var array<int,mixed> */
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

		$downgrade = !Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo]) &&
		!$photo->isVideo() &&
		$photo->size_variants->hasMedium() === true;

		$rq = request();

		// TODO: Add Live photo
		$this->size_variants = collect(SizeVariantType::cases())
			->map(fn ($v) => $photo->size_variants->getSizeVariant($v))
			->filter(fn ($e) => $e !== null)
			->map(fn ($v) => SizeVariantResource::make($v))
			->map(fn ($v, $k) => $k === 0 ? $v->setNoUrl($downgrade) : $v) // 0 = original
			->map(fn ($v) => $v->toArray($rq))
			->map(function ($v) {
				/** @var array{filesize:int} $v */
				$v['filesize'] = Helpers::getSymbolByQuantity(intval($v['filesize']));

				return $v;
			})->all();
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
