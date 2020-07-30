<?php

namespace App\Metadata;

use App\Assets\Helpers;
use App\Configs;
use App\Logs;
use PHPExif\Reader\Reader;

class Extractor
{
	/**
	 * return bare array for info.
	 *
	 * @return array
	 */
	public function bare()
	{
		return [
			'type' => '',
			'width' => 0,
			'height' => 0,
			'title' => '',
			'description' => '',
			'orientation' => '',
			'iso' => '',
			'aperture' => '',
			'make' => '',
			'model' => '',
			'shutter' => '',
			'focal' => '',
			'takestamp' => null,
			'lens' => '',
			'tags' => '',
			'position' => '',
			'latitude' => null,
			'longitude' => null,
			'altitude' => null,
			'imgDirection' => null,
			'location' => null,
			'size' => 0,
			'livePhotoContentID' => null,
			'livePhotoStillImageTime' => null,
			'MicroVideoOffset' => null,
		];
	}

	/**
	 * @param array  $metadata
	 * @param string $filename
	 */
	public function size(array &$metadata, string $filename)
	{
		// Size
		$size = filesize($filename) / 1024;
		if ($size >= 1024) {
			$metadata['size'] = round($size / 1024, 1) . ' MB';
		} else {
			$metadata['size'] = round($size, 1) . ' KB';
		}
	}

	/**
	 * Extracts metadata from an image file.
	 *
	 * @param string $filename
	 * @param  string file kind
	 *
	 * @return array
	 */
	public function extract(string $filename, string $kind): array
	{
		$reader = null;

		// Get kind of file (photo, video, raw)
		$extension = Helpers::getExtension($filename, false);

		// check raw files
		$is_raw = false;
		$raw_formats = strtolower(Configs::get_value('raw_formats', ''));
		if (in_array(strtolower($extension), explode('|', $raw_formats), true)) {
			$is_raw = true;
		}

		if ($kind !== 'video') {
			// It's a photo
			if (Configs::hasExiftool()) {
				// reader with Exiftool adapter
				$reader = Reader::factory(Reader::TYPE_EXIFTOOL);
			} elseif (Configs::hasImagick() && $is_raw) {
				// Use imagick as exif reader for raw files (broader support)
				$reader = Reader::factory(Reader::TYPE_IMAGICK);
			} else {
				// Use Php native tools
				$reader = Reader::factory(Reader::TYPE_NATIVE);
			}
		} else {
			// Let's try to use FFmpeg; if not available, let's try Exiftool
			if (Configs::hasFFmpeg()) {
				// It's a video -> use FFProbe
				$reader = Reader::factory(Reader::TYPE_FFPROBE);
			} elseif (Configs::hasExiftool()) {
				// reader with Exiftool adapter
				$reader = Reader::factory(Reader::TYPE_EXIFTOOL);
			} else {
				// Use Php native tools to extract at least MimeType and Filesize
				// For all other properties, it will not return anything
				$reader = Reader::factory(Reader::TYPE_NATIVE);
				Logs::notice(__METHOD__, __LINE__, 'FFmpeg and Exiftool not being available; Extraction of metadata limited to mime type and file size.');
			}
		}

		try {
			// this can throw an exception in the case of Exiftool adapter!
			$exif = $reader->read($filename);
		} catch (\Exception $e) {
			// Use Php native tools
			Logs::error(__METHOD__, __LINE__, $e->getMessage());
			$reader = Reader::factory(Reader::TYPE_NATIVE);
			$exif = $reader->read($filename);
		}

		// Attempt to get sidecar metadata if it exists, make sure to check 'real' path in case of symlinks
		$sidecarData = [];

		// readlink fails if it's not a link -> we need to separate it
		$realFile = $filename;
		if (is_link($filename)) {
			try {
				// if readlink($filename) == False then $realFile = $filename.
				// if readlink($filename) != False then $realFile = readlink($filename)
				$realFile = readlink($filename) ?: $filename;
			} catch (\Exception $e) {
				Logs::error(__METHOD__, __LINE__, $e->getMessage());
			}
		}
		if (Configs::hasExiftool() && file_exists($realFile . '.xmp')) {
			try {
				// Don't use the same reader as the file in case it's a video
				$sidecarReader = Reader::factory(Reader::TYPE_EXIFTOOL);
				$sidecarData = $sidecarReader->read($realFile . '.xmp')->getData();

				// We don't want to overwrite the media's type with the mimetype of the sidecar file
				unset($sidecarData['MimeType']);

				if (Configs::get_value('prefer_available_xmp_metadata', '0') == '1') {
					$exif->setData(array_merge($exif->getData(), $sidecarData));
				} else {
					$exif->setData(array_merge($sidecarData, $exif->getData()));
				}
			} catch (\Exception $e) {
				Logs::error(__METHOD__, __LINE__, $e->getMessage());
			}
		}

		$metadata = $this->bare();
		$metadata['type'] = ($exif->getMimeType() !== false) ? $exif->getMimeType() : '';
		$metadata['width'] = ($exif->getWidth() !== false) ? $exif->getWidth() : 0;
		$metadata['height'] = ($exif->getHeight() !== false) ? $exif->getHeight() : 0;
		$metadata['title'] = ($exif->getTitle() !== false) ? $exif->getTitle() : '';
		$metadata['description'] = ($exif->getDescription() !== false) ? $exif->getDescription() : '';
		$metadata['orientation'] = ($exif->getOrientation() !== false) ? $exif->getOrientation() : '';
		$metadata['iso'] = ($exif->getIso() !== false) ? $exif->getIso() : '';
		$metadata['make'] = ($exif->getMake() !== false) ? $exif->getMake() : '';
		$metadata['model'] = ($exif->getCamera() !== false) ? $exif->getCamera() : '';
		$metadata['shutter'] = ($exif->getExposure() !== false) ? $exif->getExposure() : '';
		$metadata['lens'] = ($exif->getLens() !== false) ? $exif->getLens() : '';
		$metadata['tags'] = ($exif->getKeywords() !== false) ? (is_array($exif->getKeywords()) ? implode(',', $exif->getKeywords()) : $exif->getKeywords()) : '';
		$metadata['latitude'] = ($exif->getLatitude() !== false) ? $exif->getLatitude() : null;
		$metadata['longitude'] = ($exif->getLongitude() !== false) ? $exif->getLongitude() : null;
		$metadata['altitude'] = ($exif->getAltitude() !== false) ? $exif->getAltitude() : null;
		$metadata['imgDirection'] = ($exif->getImgDirection() !== false) ? $exif->getImgDirection() : null;
		$metadata['size'] = ($exif->getFileSize() !== false) ? $exif->getFileSize() : 0;
		$metadata['livePhotoContentID'] = ($exif->getContentIdentifier() !== false) ? $exif->getContentIdentifier() : null;
		$metadata['MicroVideoOffset'] = ($exif->getMicroVideoOffset() !== false) ? $exif->getMicroVideoOffset() : null;

		$takestamp = $exif->getCreationDate();
		if ($takestamp !== false) {
			// Some videos use local time while others use UTC and it's
			// often impossible to tell.  We rely here on a simple
			// filetype-based heuristics and, if needed, convert a UTC
			// timestamp to the local time of the Lychee server so that
			// it's displayed to users as local time.
			//
			// Other possible approaches would include deriving local time from
			// the file name or from other objects in the same album, as well
			// as extracting the time zone from the location data if present.
			if ($kind === 'video') {
				$locals = strtolower(Configs::get_value('local_takestamp_video_formats', ''));
				if (!in_array(strtolower($extension), explode('|', $locals), true)) {
					// This is a video format where we expect the takestamp
					// to be provided in UTC .
					if ($takestamp->getTimezone()->getName() === date_default_timezone_get()) {
						// Most likely the time zone info was missing so the
						// system default was used instead, which is wrong,
						// because the takestamp is in UTC.  We recreate it as
						// UTC timestamp and _then_ change the time zone to local.
						$takestamp = new \DateTime($takestamp->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
						$takestamp->setTimezone(new \DateTimeZone(date_default_timezone_get()));
					} elseif ($takestamp->getTimezone()->getName() === 'Z') {
						// This one is correctly in Zulu (UTC).  We just need
						// to change the time zone to local.
						$takestamp->setTimezone(new \DateTimeZone(date_default_timezone_get()));
					} else {
						// In the remaining cases a time zone was extracted
						// from the files so we don't mess with it.
					}
				}
			}

			// We need to make sure that the takestamp is between '1970-01-01 00:00:01' UTC and '2038-01-19 03:14:07' UTC.
			// We set the value to null in case we're out of bounds
			$min_date = new \DateTime('1970-01-01 00:00:01', new \DateTimeZone('UTC'));
			$max_date = new \DateTime('2038-01-19 03:14:07', new \DateTimeZone('UTC'));
			if ($takestamp < $min_date || $takestamp > $max_date) {
				$metadata['takestamp'] = null;
				Logs::notice(__METHOD__, __LINE__, 'Takestamp (' . $takestamp->format('Y-m-d H:i:s') . ') out of bounds (needs to be between 1970-01-01 00:00:01 and 2038-01-19 03:14:07)');
			} else {
				$metadata['takestamp'] = $takestamp->format('Y-m-d H:i:s');
			}
		} else {
			$metadata['takestamp'] = null;
		}

		// We need to make sure, latitude is between -90/90 and longitude is between -180/180
		// We set values to null in case we're out of bounds
		if ($metadata['latitude'] !== null || $metadata['longitude'] !== null) {
			$latitude = $metadata['latitude'];
			$longitude = $metadata['longitude'];
			if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
				$metadata['latitude'] = null;
				$metadata['longitude'] = null;
				Logs::notice(__METHOD__, __LINE__, 'Latitude/Longitude (' . $latitude . '/' . $longitude . ') out of bounds (needs to be between -90/90 and -180/180)');
			}
		}

		// We need to make sure, altitude is between -999999.9999 and 999999.9999
		// We set values to null in case we're out of bounds
		if ($metadata['altitude'] !== null) {
			$altitude = $metadata['altitude'];
			if ($altitude < -999999.9999 || $altitude > 999999.9999) {
				$metadata['altitude'] = null;
				Logs::notice(__METHOD__, __LINE__, 'Altitude (' . $altitude . ') out of bounds for database (needs to be between -999999.9999 and 999999.9999)');
			}
		}

		// We need to make sure, imgDirection is between 0 and 360
		// We set values to null in case we're out of bounds
		if ($metadata['imgDirection'] !== null) {
			$imgDirection = $metadata['imgDirection'];
			if ($imgDirection < 0 || $imgDirection > 360) {
				$metadata['imgDirection'] = null;
				Logs::notice(__METHOD__, __LINE__, 'GPSImgDirection (' . $imgDirection . ') out of bounds (needs to be between 0 and 360)');
			}
		}

		// Position
		$fields = [];
		if ($exif->getCity() !== false) {
			$fields[] = trim($exif->getCity());
		}
		if ($exif->getSublocation() !== false) {
			$fields[] = trim($exif->getSublocation());
		}
		if ($exif->getState() !== false) {
			$fields[] = trim($exif->getState());
		}
		if ($exif->getCountry() !== false) {
			$fields[] = trim($exif->getCountry());
		}
		if (!empty($fields)) {
			$metadata['position'] = implode(', ', $fields);
		}

		if ($kind !== 'video') {
			$metadata['aperture'] = ($exif->getAperture() !== false) ? $exif->getAperture() : '';
			$metadata['focal'] = ($exif->getFocalLength() !== false) ? $exif->getFocalLength() : '';
			if ($metadata['focal'] !== '') {
				$metadata['focal'] = round($metadata['focal']) . ' mm';
			}
		} else {
			// Video -> reuse fields
			$metadata['aperture'] = ($exif->getDuration() !== false) ? $exif->getDuration() : '';
			$metadata['focal'] = ($exif->getFramerate() !== false) ? $exif->getFramerate() : '';
		}

		if ($metadata['title'] == '') {
			$metadata['title'] = ($exif->getHeadline() !== false) ? $exif->getHeadline() : '';
		}

		if ($metadata['shutter'] !== '') {
			$metadata['shutter'] = $metadata['shutter'] . ' s';
		}
		if ($metadata['size'] > 0) {
			$metadata['size'] = $metadata['size'] / 1024;
			if ($metadata['size'] >= 1024) {
				$metadata['size'] = round($metadata['size'] / 1024, 1) . ' MB';
			} else {
				$metadata['size'] = round($metadata['size'], 1) . ' KB';
			}
		}

		// Decode location data
		$metadata['location'] = Geodecoder::decodeLocation($metadata['latitude'], $metadata['longitude']);

		return $metadata;
	}
}
