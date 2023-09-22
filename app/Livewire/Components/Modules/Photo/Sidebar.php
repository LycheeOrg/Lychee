<?php

namespace App\Livewire\Components\Modules\Photo;

use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Facades\Helpers;
use App\Models\Configs;
use App\Models\Photo as PhotoModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This is the side bar in the case of Photo.
 *
 * Contrary to the JS implementation, the attributes are directly embeded in the bar.
 * This will (hopefully) simplify the update when editing properties.
 */
class Sidebar extends Component
{
	#[Locked] public string $title;
	#[Locked] public string $description;
	#[Locked] public string $created_at;
	#[Locked] public string $filesize;
	#[Locked] public string $type;
	#[Locked] public string $resolution;
	/** @var array<int,string> */
	#[Locked] public array $tags;
	#[Locked] public bool $is_public;
	#[Locked] public bool $is_video;
	#[Locked] public bool $has_password;
	#[Locked] public string $owner_name = '';
	#[Locked] public string $license;
	#[Locked] public string $duration = '';
	#[Locked] public string $fps = '';
	#[Locked] public ?string $model;
	#[Locked] public ?string $make;
	#[Locked] public string $lens;
	#[Locked] public bool $has_exif = false;
	#[Locked] public string $iso;
	#[Locked] public string $focal;
	#[Locked] public string $aperture;
	#[Locked] public string $shutter;
	#[Locked] public ?string $taken_at;
	#[Locked] public bool $has_location = false;
	#[Locked] public string $latitude;
	#[Locked] public string $longitude;
	#[Locked] public string $altitude;
	#[Locked] public ?string $location;
	#[Locked] public ?float $img_direction;
	/**
	 * Given a photo model extract all the information.
	 * ! It is possible that we may need to extract those in a similar fashion as with Album.
	 *
	 * @param PhotoModel $photo
	 *
	 * @return void
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	public function mount(PhotoModel $photo): void
	{
		$date_format_uploaded = Configs::getValueAsString('date_format_sidebar_uploaded');
		$date_format_taken_at = Configs::getValueAsString('date_format_sidebar_taken_at');

		$this->title = $photo->title;
		$this->created_at = $photo->created_at->format($date_format_uploaded);
		$this->description = $photo->description ?? '';

		$this->type = $photo->type;
		$original = $photo->size_variants->getOriginal();
		$this->filesize = Helpers::getSymbolByQuantity($original->filesize);
		$this->resolution = $original->width . ' x ' . $original->height;

		$this->is_video = $photo->isVideo();
		$this->license = $photo->license->localization();

		$this->taken_at = $photo->taken_at?->format($date_format_taken_at);
		$this->make = $photo->make;
		$this->model = $photo->model;
		$this->tags = $photo->tags;

		$this->has_exif = $this->genExifHash($photo) !== '';
		if ($this->has_exif) {
			$this->lens = $photo->lens ?? '';
			$this->shutter = $photo->shutter ?? '';
			$this->aperture = str_replace('f/', '', $photo->aperture);
			$this->focal = $photo->focal ?? '';
			$this->iso = $photo->iso ?? '';
		}

		if ($this->is_video) {
			$this->duration = Helpers::secondsToHMS(intval($photo->aperture));
			$this->fps = $photo->focal ?? '';
		}

		$this->has_location = $this->has_location($photo);
		if ($this->has_location) {
			$this->latitude = $this->decimalToDegreeMinutesSeconds($photo->latitude, true);
			$this->longitude = $this->decimalToDegreeMinutesSeconds($photo->longitude, false);
			$this->altitude = round($photo->altitude, 1) . 'm';
			$this->location = $photo->location;
			$this->img_direction = $photo->img_direction;
		}
	}

	/**
	 * Render the view.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('livewire.modules.photo.sidebar');
	}

	/**
	 * Converts a decimal degree into integer degree, minutes and seconds.
	 *
	 * TODO: Consider to make this method part of `lychee.locale`.
	 *
	 * @param float $decimal
	 * @param bool  $type    - indicates if the passed decimal indicates a
	 *                       latitude (`true`) or a longitude (`false`)
	 *
	 * @returns string
	 */
	private function decimalToDegreeMinutesSeconds(float $decimal, bool $type): string
	{
		$d = abs($decimal);

		// absolute value of decimal must be smaller than 180;
		if ($d > 180) {
			return '';
		}

		// set direction; north assumed
		if ($type && $decimal < 0) {
			$direction = 'S';
		} elseif (!$type && $decimal < 0) {
			$direction = 'W';
		} elseif (!$type) {
			$direction = 'E';
		} else {
			$direction = 'N';
		}

		// get degrees
		$degrees = floor($d);

		// get seconds
		$seconds = ($d - $degrees) * 3600;

		// get minutes
		$minutes = floor($seconds / 60);

		// reset seconds
		$seconds = floor($seconds - $minutes * 60);

		return $degrees . '° ' . $minutes . "' " . $seconds . '" ' . $direction;
	}

	private function genExifHash(PhotoModel $photo): string
	{
		$exifHash = $photo->make;
		$exifHash .= $photo->model;
		$exifHash .= $photo->shutter;
		if (!$photo->isVideo()) {
			$exifHash .= $photo->aperture;
			$exifHash .= $photo->focal;
		}
		$exifHash .= $photo->iso;

		return $exifHash;
	}

	private function has_location(PhotoModel $photo): bool
	{
		return $photo->longitude !== null && $photo->latitude !== null && $photo->altitude !== null;
	}
}
