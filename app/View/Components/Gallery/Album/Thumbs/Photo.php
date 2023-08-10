<?php

namespace App\View\Components\Gallery\Album\Thumbs;

use App\Enum\Livewire\AlbumMode;
use App\Enum\SizeVariantType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Models\Configs;
use App\Models\Extensions\SizeVariants;
use App\Models\Photo as ModelsPhoto;
use App\Models\SizeVariant;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\URL;
use Illuminate\View\Component;
use LycheeOrg\PhpFlickrJustifiedLayout\DTO\Item;

class Photo extends Component
{
	public string $class_thumbs = '';
	public AlbumMode $layout;

	public string $album_id = '';
	public string $photo_id = '';

	public bool $is_lazyload = true;
	public bool $is_video = false;

	public string $title;
	public ?string $taken_at;
	public string $created_at;
	public bool $is_starred = false;
	public bool $is_public = false;

	public string $src = '';
	public string $srcset = '';
	public string $srcset2x = '';

	public int $_w = 200;
	public int $_h = 200;

	/** Use by the Justified layout */
	public string $style = '';

	/**
	 * Create a new component instance.
	 *
	 * @return void
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct(ModelsPhoto $data, string $albumId, Item|null $geometry, AlbumMode $layout)
	{
		$this->layout = $layout;
		$date_format = Configs::getValueAsString('date_format_photo_thumb');

		$this->album_id = $albumId;
		$this->photo_id = $data->id;
		$this->title = $data->title;
		$this->taken_at = $data->taken_at?->format($date_format) ?? '';
		$this->created_at = $data->created_at->format($date_format);
		$this->is_starred = $data->is_starred;
		$this->style = '';
		$this->is_video = $data->isVideo();

		if ($this->layout === AlbumMode::SQUARE) {
			$this->setSquareLayout($data);

			return;
		}

		// Not squared layout:
		// - justified
		// - Masondry
		// - grid
		$this->style = $geometry?->toCSS() ?? '';

		$this->setOtherLayouts($data);
	}

	/**
	 * Define src.
	 *
	 * this is what will be first deplayed before loading.
	 *
	 * @param SizeVariant|null $thumb
	 * @param bool             $is_video
	 * @param bool             $has_live_photo_url
	 *
	 * @return void
	 */
	private function set_src(?SizeVariant $thumb, bool $is_video, bool $has_live_photo_url): void
	{
		// default is place holder
		$this->src = URL::asset('img/placeholder.png');

		// if thumb is not null then directly return:
		// it will be replaced later by src-set
		if ($thumb !== null) {
			$this->src = sprintf("src='%s'", $this->src);

			return;
		}

		// TODO: Don't hardcode paths
		// change the png in the other cases.
		// no need to lazyload too.
		$this->is_lazyload = false;
		$this->src = $is_video ? URL::asset('img/play-icon.png') : $this->src;
		$this->src = $has_live_photo_url ? URL::asset('img/live-photo-icon.png') : $this->src;
		$this->src = sprintf("src='%s'", $this->src);
	}

	/**
	 * Defines the thumbs if the layout is squared.
	 *
	 * @param ModelsPhoto $data
	 *
	 * @return void
	 *
	 * @throws IllegalOrderOfOperationException
	 * @throws InvalidSizeVariantException
	 */
	private function setSquareLayout(ModelsPhoto $data): void
	{
		$has_live_photo_url = $data->live_photo_url !== null;

		$thumb = $data->size_variants->getSizeVariant(SizeVariantType::THUMB);
		$thumb2x = $data->size_variants->getSizeVariant(SizeVariantType::THUMB2X);

		$this->class_thumbs = '';
		$this->class_thumbs .= $this->is_video ? ' video' : '';
		$this->class_thumbs .= $has_live_photo_url ? ' livephoto' : '';

		$thumbUrl = $thumb?->url;
		$thumb2xUrl = $thumb2x?->url;
		$this->set_src($thumb, $this->is_video, $has_live_photo_url);

		$this->srcset = sprintf("data-src='%s'", URL::asset($thumbUrl));
		$this->srcset2x = $thumb2xUrl !== null ? sprintf("data-srcset='%s 2x'", URL::asset($thumb2xUrl)) : '';
	}

	/**
	 * Not squared layout:
	 * - justified
	 * - Masondry
	 * - grid.
	 *
	 * @param ModelsPhoto $data
	 *
	 * @return void
	 *
	 * @throws IllegalOrderOfOperationException
	 * @throws InvalidSizeVariantException
	 */
	private function setOtherLayouts(ModelsPhoto $data): void
	{
		$is_video = $data->isVideo();
		$has_live_photo_url = $data->live_photo_url !== null;

		$thumb = $data->size_variants->getSizeVariant(SizeVariantType::THUMB);
		$thumb2x = $data->size_variants->getSizeVariant(SizeVariantType::THUMB2X);

		$this->class_thumbs = '';
		$this->class_thumbs .= $is_video ? ' video' : '';
		$this->class_thumbs .= $has_live_photo_url ? ' livephoto' : '';

		$this->set_src($thumb, $is_video, $has_live_photo_url);

		$dim = 200;
		$dim2x = 400;
		$thumbUrl = $thumb?->url;
		$thumb2xUrl = $thumb2x?->url;

		// Probably this code needs some fix/refactoring, too. However, where is this method invoked and
		// what is the structure of the passed `data` array? (Could find any invocation.)

		if ($data->size_variants->hasMediumOrSmall()) {
			$thumbsUrls = $this->setThumbUrls($data->size_variants);

			$this->_w = $thumbsUrls['w'];
			$this->_h = $thumbsUrls['h'];
			$dim2x = $thumbsUrls['w2x'];
			$thumbUrl = $thumbsUrls['thumbUrl'];
			$thumb2xUrl = $thumbsUrls['thumb2xUrl'];

			$dim = $this->_w;
		} elseif (!$data->isVideo()) {
			$original = $data->size_variants->getSizeVariant(SizeVariantType::ORIGINAL);

			$this->_w ??= $original->width;
			$this->_h ??= $original->height;
			// Fallback for images with no small or medium.
			$thumbUrl ??= $original->url;
		} elseif ($thumbUrl === null) {
			// Fallback for videos with no small (the case of no thumb is handled else where).
			$this->class_thumbs = 'video';
			$dim = 200;
			$dim2x = 200;
		}

		$this->srcset = sprintf("data-src='%s'", URL::asset($thumbUrl));
		$this->srcset2x = $thumb2xUrl !== null ? sprintf("data-srcset='%s %dw, %s %dw'", URL::asset($thumbUrl), $dim, URL::asset($thumb2xUrl), $dim2x) : '';
	}

	/**
	 * Fetch the thumbs data.
	 *
	 * @param SizeVariants $sizeVariants
	 *
	 * @return array{w:int,w2x:int|null,h:int,thumbUrl:string,thumb2xUrl:string|null}
	 *
	 * @throws InvalidSizeVariantException
	 */
	private function setThumbUrls(SizeVariants $sizeVariants): array
	{
		$small = $sizeVariants->getSizeVariant(SizeVariantType::SMALL);
		$small2x = $sizeVariants->getSizeVariant(SizeVariantType::SMALL2X);
		$medium = $sizeVariants->getSizeVariant(SizeVariantType::MEDIUM);
		$medium2x = $sizeVariants->getSizeVariant(SizeVariantType::MEDIUM2X);

		/** @var int $w */
		$w = $small?->width ?? $medium?->width;
		$w2x = $small2x?->width ?? $medium2x?->width;
		/** @var int $h */
		$h = $small?->height ?? $medium?->height;

		/** @var string $thumbUrl */
		$thumbUrl = $small?->url ?? $medium?->url;
		$thumb2xUrl = $small2x?->url ?? $medium2x?->url;

		return ['w' => $w, 'w2x' => $w2x, 'h' => $h, 'thumbUrl' => $thumbUrl, 'thumb2xUrl' => $thumb2xUrl];
	}

	/**
	 * Get the view / contents that represent the component.
	 *
	 * @return \Illuminate\Contracts\View\View|\Closure|string
	 *
	 * @throws BindingResolutionException
	 */
	public function render()
	{
		return view('components.gallery.album.thumbs.photo');
	}
}
