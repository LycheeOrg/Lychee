<?php

namespace App\View\Components;

use App\Enum\SizeVariantType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use App\Models\Photo as ModelsPhoto;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\URL;
use Illuminate\View\Component;

class Photo extends Component
{
	public string $class = '';

	public string $album_id = '';
	public string $photo_id = '';

	public bool $is_lazyload = true;

	public string $title;
	public ?string $taken_at;
	public string $created_at;
	public bool $is_starred = false;
	public bool $is_public = false;

	public string $src = '';
	public string $srcset = '';
	public string $srcset2x = '';

	public bool $is_square_layout = false;
	public int $_w = 200;
	public int $_h = 200;

	/**
	 * Create a new component instance.
	 *
	 * @return void
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct(ModelsPhoto $data)
	{
		$this->album_id = $data->album?->id ?? '';
		$this->photo_id = $data->id;
		$this->title = $data->title;
		$this->taken_at = $data->taken_at ?? '';
		$this->created_at = $data->created_at;
		$this->is_starred = $data->is_starred;
		// $this->is_public = $data->is_public;

		$this->class = '';
		$this->class .= $data->isVideo() ? ' video' : '';
		$this->class .= $data->live_photo_url !== null ? ' livephoto' : '';

		$this->is_square_layout = Configs::getValueAsInt('layout') === 0;

		$this->src = URL::asset('img/placeholder.png');

		// TODO: Don't hardcode paths
		if ($data->size_variants->getSizeVariant(SizeVariantType::THUMB) === null) {
			$this->src = $data->isVideo() ? URL::asset('img/play-icon.png') : $this->src;
			$this->src = $data->live_photo_url !== null ? URL::asset('img/live-photo-icon.png') : $this->src;

			$this->is_lazyload = false;
		}

		$dim = 0;
		$dim2x = 0;
		$thumb2x = '';
		$thumb2xUrl = '';

		$thumb = $data->size_variants->getSizeVariant(SizeVariantType::THUMB);
		$thumb2x = $data->size_variants->getSizeVariant(SizeVariantType::THUMB2X);
		$small = $data->size_variants->getSizeVariant(SizeVariantType::SMALL);
		$small2x = $data->size_variants->getSizeVariant(SizeVariantType::SMALL2X);
		$medium = $data->size_variants->getSizeVariant(SizeVariantType::MEDIUM);
		$medium2x = $data->size_variants->getSizeVariant(SizeVariantType::MEDIUM2X);
		$original = $data->size_variants->getSizeVariant(SizeVariantType::ORIGINAL);

		// Probably this code needs some fix/refactoring, too. However, where is this method invoked and
		// what is the structure of the passed `data` array? (Could find any invocation.)
		if ($this->is_square_layout) {
			$thumbUrl = $thumb?->url;
			$thumb2xUrl = $thumb2x?->url;
		} elseif ($small !== null) {
			$this->_w = $small->width;
			$this->_h = $small->height;
			$thumbUrl = $small->url;
			$thumb2xUrl = $small2x->url ?? '';
			$dim = $small->width;
			$dim2x = $small2x->width ?? 0;
		} elseif ($medium !== null) {
			$this->_w = $medium->width;
			$this->_h = $medium->height;
			$thumbUrl = $medium->url;
			$thumb2xUrl = $medium2x->url ?? '';
			$dim = $medium->width;
			$dim2x = $medium2x->width ?? 0;
		} elseif (!$data->isVideo()) {
			$this->_w = $original->width;
			$this->_h = $original->height;
			// Fallback for images with no small or medium.
			$thumbUrl = $original->url;
		} else {
			// Fallback for videos with no small (the case of no thumb is handled else where).
			$this->class = 'video';
			$thumbUrl = $thumb?->url;
			$thumb2xUrl = $thumb2x?->url;
			$dim = 200;
			$dim2x = 200;
		}

		$this->src = sprintf("src='%s'", $this->src);
		$this->srcset = sprintf("data-src='%s'", URL::asset($thumbUrl));

		if ($this->is_square_layout) {
			$thumb2x_src = sprintf("data-srcset='%s 2x'", URL::asset($thumb2xUrl));
		} else {
			$thumb2x_src = sprintf("data-srcset='%s %dw, %s %dw'", URL::asset($thumbUrl), $dim, URL::asset($thumb2xUrl), $dim2x);
		}

		$this->srcset2x = $thumb2xUrl !== '' ? $thumb2x_src : '';
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
		return view('components.gallery.photo');
	}
}
