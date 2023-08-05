<?php

namespace App\View\Components\Gallery\Photo;

use App\Facades\Helpers;
use App\Models\Photo;
use Illuminate\View\Component;
use Illuminate\View\View;

class Overlay extends Component
{
	public string $title;
	public string $description;
	public bool $is_camera_date;
	public bool $is_video;
	public string $date;

	public string $make;
	public string $model;
	public string $lens;

	// photo specific
	public string $shutter;
	public string $aperture;
	public string $focal;
	public string $iso;

	// video specific
	public string $duration = '';
	public string $fps = '';

	/**
	 * Mount the photo model and initialize the Component.
	 *
	 * @param Photo $photo
	 *
	 * @return void
	 */
	public function __construct(Photo $photo)
	{
		$this->title = $photo->title ?? '';
		$this->description = $photo->description ?? '';
		$this->is_video = $photo->isVideo();
		$this->is_camera_date = $photo->taken_at !== null;
		$this->date = ($photo->taken_at ?? $photo->created_at)->format('M j, Y, g:i:s A T') ?? '';

		$this->make = $photo->make ?? '';
		$this->model = $photo->model ?? '';
		$this->shutter = str_replace('s', 'sec', $photo->shutter ?? '');
		$this->aperture = str_replace('f/', '', $photo->aperture ?? '');
		$this->focal = $photo->focal ?? '';
		$this->iso = $photo->iso ?? '';
		$this->lens = $photo->lens ?? '';

		if ($this->is_video) {
			$this->duration = Helpers::secondsToHMS(intval($photo->aperture));
			$this->fps = $photo->focal ?? '';
		}
	}

	/**
	 * Render the component.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('components.gallery.photo.overlay');
	}
}