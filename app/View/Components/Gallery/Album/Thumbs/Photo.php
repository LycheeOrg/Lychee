<?php

declare(strict_types=1);

namespace App\View\Components\Gallery\Album\Thumbs;

use App\Enum\SizeVariantType;
use App\Enum\ThumbOverlayVisibilityType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Models\Configs;
use App\Models\Extensions\SizeVariants;
use App\Models\Photo as ModelsPhoto;
use App\Models\SizeVariant;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\URL;
use Illuminate\View\Component;

class Photo extends Component
{
	public string $album_id = '';
	public string $photo_id = '';

	public bool $is_lazyload = true;
	public bool $is_video = false;
	public bool $is_livephoto = false;

	public int $idx;
	public string $title;
	public ?string $taken_at;
	public string $created_at;
	public bool $is_cover_id = false;

	public string $css_overlay;

	public string $src = '';
	public string $srcset = '';
	public string $srcset2x = '';

	public int $_w = 200;
	public int $_h = 200;

	/**
	 * Create a new component instance.
	 *
	 * @return void
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct(ModelsPhoto $data, string $albumId, int $idx, ?string $coverId)
	{
		$this->idx = $idx;
		$date_format = Configs::getValueAsString('date_format_photo_thumb');
		$displayOverlay = Configs::getValueAsEnum('display_thumb_photo_overlay', ThumbOverlayVisibilityType::class);

		$this->album_id = $albumId;
		$this->photo_id = $data->id;
		$this->title = $data->title;
		$this->taken_at = $data->taken_at?->format($date_format) ?? '';
		$this->created_at = $data->created_at->format($date_format);

		$this->is_video = $data->isVideo();
		$this->is_livephoto = $data->live_photo_url !== null;
		$this->is_cover_id = $coverId === $data->id;

		$this->css_overlay = match ($displayOverlay) {
			ThumbOverlayVisibilityType::NEVER => 'hidden',
			ThumbOverlayVisibilityType::HOVER => 'opacity-0 group-hover:opacity-100 transition-all ease-out',
			default => '',
		};

		$thumb = $data->size_variants->getSizeVariant(SizeVariantType::THUMB);
		$thumb2x = $data->size_variants->getSizeVariant(SizeVariantType::THUMB2X);

		$this->set_src($thumb);

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
			$dim = 200;
			$dim2x = 200;
		}

		$this->srcset = sprintf("data-src='%s'", $thumbUrl);
		$this->srcset2x = $thumb2xUrl !== null ? sprintf("data-srcset='%s %dw, %s %dw'", $thumbUrl, $dim, $thumb2xUrl, $dim2x) : '';
	}

	/**
	 * Define src.
	 *
	 * this is what will be first deplayed before loading.
	 *
	 * @param SizeVariant|null $thumb
	 *
	 * @return void
	 */
	private function set_src(?SizeVariant $thumb): void
	{
		// default is place holder
		$this->src = URL::asset('img/placeholder.png');

		// if thumb is not null then directly return:
		// it will be replaced later by src-set
		if ($thumb !== null) {
			$this->src = sprintf("src='%s'", $this->src);

			return;
		}

		// change the png in the other cases.
		// no need to lazyload too.
		$this->is_lazyload = false;
		$this->src = $this->is_video ? URL::asset('img/play-icon.png') : $this->src;
		$this->src = $this->is_livephoto ? URL::asset('img/live-photo-icon.png') : $this->src;
		$this->src = sprintf("src='%s'", $this->src);
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
