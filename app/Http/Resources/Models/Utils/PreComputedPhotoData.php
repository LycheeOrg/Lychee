<?php

namespace App\Http\Resources\Models\Utils;

use App\Models\Photo;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PreComputedPhotoData extends Data
{
	public bool $is_video;
	public bool $is_raw;
	public bool $is_livephoto;
	public bool $is_camera_date;
	public bool $has_exif;
	public bool $has_location;

	public function __construct(Photo $photo)
	{
		$this->is_video = $photo->isVideo();
		$this->is_raw = $photo->isRaw();
		$this->is_livephoto = $photo->live_photo_url !== null;
		$this->is_camera_date = $photo->taken_at !== null;
		$this->has_exif = $this->genExifHash($photo) !== '';
		$this->has_location = $this->has_location($photo);
	}

	private function has_location(Photo $photo): bool
	{
		return $photo->longitude !== null &&
			$photo->latitude !== null &&
			$photo->altitude !== null;
	}

	private function genExifHash(Photo $photo): string
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
}
