<?php

namespace App\Metadata;

use App\Exceptions\ExternalComponentFailedException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\MediaFileOperationException;
use App\Image\NativeLocalFile;
use App\Models\Configs;
use App\Models\Logs;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Support\Carbon;
use PHPExif\Adapter\NoAdapterException;
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
			'orientation' => 1,
			'iso' => '',
			'aperture' => '',
			'make' => '',
			'model' => '',
			'shutter' => '',
			'focal' => '',
			'taken_at' => null,
			'lens' => '',
			'tags' => '',
			'position' => '',
			'latitude' => null,
			'longitude' => null,
			'altitude' => null,
			'imgDirection' => null,
			'location' => null,
			'filesize' => 0,
			'livePhotoContentID' => null,
			'livePhotoStillImageTime' => null,
			'MicroVideoOffset' => null,
			'checksum' => null,
		];
	}

	/**
	 * Returns the SHA-1 checksum of a file.
	 *
	 * @param NativeLocalFile $file the file
	 *
	 * @return string the checksum
	 *
	 * @throws MediaFileOperationException
	 */
	public function checksum(NativeLocalFile $file): string
	{
		$checksum = sha1_file($file->getAbsolutePath());
		if ($checksum === false) {
			throw new MediaFileOperationException('Could not compute checksum for: ' . $file->getAbsolutePath());
		}

		return $checksum;
	}

	/**
	 * Extracts metadata from a file.
	 *
	 * @param NativeLocalFile $file the file
	 *
	 * @return array
	 *
	 * @throws ExternalComponentMissingException
	 * @throws MediaFileOperationException
	 * @throws ExternalComponentFailedException
	 */
	public function extract(NativeLocalFile $file): array
	{
		$reader = null;

		// TODO: This line is extremely dangerous, because it tries to determine the type of file based on a possibly not existing file extension
		// Note: For temporarily stored files during upload, PHP normally uses
		// temporary file names without an extension.
		// We should stop passing around absolute file paths and try to
		// re-determine the MIME type over and over again, but pass around
		// proper `File` objects which also hold the MIME type which has
		// been established initially.
		$extension = $file->getExtension();

		try {
			if ($file->isSupportedImage()) {
				// It's a photo
				if (Configs::hasExiftool()) {
					// reader with Exiftool adapter
					$reader = Reader::factory(Reader::TYPE_EXIFTOOL);
				} elseif (Configs::hasImagick()) {
					// Use imagick as exif reader if available
					$reader = Reader::factory(Reader::TYPE_IMAGICK);
				} else {
					// Use Php native tools
					$reader = Reader::factory(Reader::TYPE_NATIVE);
				}
			} elseif ($file->isSupportedVideo()) {
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
			} else {
				// It is an accepted raw file
				if (Configs::hasImagick()) {
					// Use imagick as exif reader for raw files (broader support)
					$reader = Reader::factory(Reader::TYPE_IMAGICK);
				} else {
					// Use Php native tools
					$reader = Reader::factory(Reader::TYPE_NATIVE);
				}
			}

			// this can throw an exception in the case of Exiftool adapter!
			// TODO: This may fail for files without an extension.
			// In particular, PHPExif uses another method again to determine
			// the MIME type of a file.
			// For example, the adapter `PHPExif\Adapter\FFprobe` uses
			// `mime_content_type`, but our upload controller uses the
			// Symfony MIME utilities.
			// The adapter `PHPExif\Adapter\FFprobe` has already been equipped
			// with a work-around for MP4 videos which are wrongly classified
			// as `application/octet-stream`, but this work-around only
			// succeeds if the file has a recognized extension.
			$exif = $reader->read($file->getAbsolutePath());
		} catch (\InvalidArgumentException|NoAdapterException $e) {
			throw new ExternalComponentMissingException('The configured EXIF adapter is not available', $e);
		} catch (\RuntimeException $e) {
			// thrown by $reader->read if EXIF could not be extracted,
			// don't give up yet, only log the event
			report($e);
			$exif = false;
		}

		if ($exif === false) {
			try {
				Logs::notice(__METHOD__, __LINE__, 'Falling back to native adapter.');
				// Use Php native tools
				$reader = Reader::factory(Reader::TYPE_NATIVE);
				$exif = $reader->read($file->getAbsolutePath());
			} catch (\InvalidArgumentException|NoAdapterException $e) {
				throw new ExternalComponentMissingException('The configured EXIF adapter is not available', $e);
			} catch (\RuntimeException $e) {
				// thrown by $reader->read if EXIF could not be extracted,
				// even with the native adapter, now we give up
				throw new MediaFileOperationException('Could not even extract basic EXIF data with the native adapter', $e);
			}
		}

		// Attempt to get sidecar metadata if it exists, make sure to check 'real' path in case of symlinks
		$sidecarData = [];

		$sidecarFile = new NativeLocalFile($file->getAbsolutePath() . '.xmp');

		if (Configs::hasExiftool() && $sidecarFile->exists()) {
			try {
				// Don't use the same reader as the file in case it's a video
				$sidecarReader = Reader::factory(Reader::TYPE_EXIFTOOL);
				$sidecarData = $sidecarReader->read($sidecarFile->getAbsolutePath())->getData();

				// We don't want to overwrite the media's type with the mimetype of the sidecar file
				unset($sidecarData['MimeType']);

				if (Configs::get_value('prefer_available_xmp_metadata', '0') == '1') {
					$exif->setData(array_merge($exif->getData(), $sidecarData));
				} else {
					$exif->setData(array_merge($sidecarData, $exif->getData()));
				}
			} catch (\Exception $e) {
				report($e);
			}
		}

		$metadata = $this->bare();
		$metadata['type'] = ($exif->getMimeType() !== false) ? $exif->getMimeType() : $file->getMimeType();
		$metadata['width'] = ($exif->getWidth() !== false) ? $exif->getWidth() : 0;
		$metadata['height'] = ($exif->getHeight() !== false) ? $exif->getHeight() : 0;
		$metadata['title'] = ($exif->getTitle() !== false) ? $exif->getTitle() : '';
		$metadata['description'] = ($exif->getDescription() !== false) ? $exif->getDescription() : '';
		$metadata['orientation'] = ($exif->getOrientation() !== false) ? $exif->getOrientation() : 1;
		$metadata['iso'] = ($exif->getIso() !== false) ? $exif->getIso() : '';
		$metadata['make'] = ($exif->getMake() !== false) ? $exif->getMake() : '';
		$metadata['model'] = ($exif->getCamera() !== false) ? $exif->getCamera() : '';
		$metadata['shutter'] = ($exif->getExposure() !== false) ? $exif->getExposure() : '';
		$metadata['lens'] = ($exif->getLens() !== false) ? $exif->getLens() : '';
		$metadata['tags'] = ($exif->getKeywords() !== false) ? $exif->getKeywords() : [];
		$metadata['latitude'] = ($exif->getLatitude() !== false) ? $exif->getLatitude() : null;
		$metadata['longitude'] = ($exif->getLongitude() !== false) ? $exif->getLongitude() : null;
		$metadata['altitude'] = ($exif->getAltitude() !== false) ? $exif->getAltitude() : null;
		$metadata['imgDirection'] = ($exif->getImgDirection() !== false) ? $exif->getImgDirection() : null;
		$metadata['filesize'] = ($exif->getFileSize() !== false) ? $exif->getFileSize() : 0;
		$metadata['live_photo_content_id'] = ($exif->getContentIdentifier() !== false) ? $exif->getContentIdentifier() : null;
		$metadata['MicroVideoOffset'] = ($exif->getMicroVideoOffset() !== false) ? $exif->getMicroVideoOffset() : null;
		$metadata['checksum'] = $this->checksum($file);

		$taken_at = $exif->getCreationDate();
		if ($taken_at !== false) {
			try {
				$taken_at = Carbon::instance($taken_at);
				// There are three different timezone which needs to considered:
				//
				//  a) The original timezone of the location where the photo has
				//     been taken
				//  b) The timezone of the server which is running the Lychee
				//     backend
				//  c) The timezone of the beholder who is looking at the photo
				//     with his/her/their web browser
				//
				// **Notes about a):**
				//
				// For best human interaction with photos the date/time when the
				// photo has been taken should be based on the local timezone of
				// the location where the photo has been taken.
				// This matches the beholder's expectation; e.g. a photo of a
				// sunset should show a "wall time" around the early evening,
				// while a breakfast photo should show a "wall time" in the
				// morning.
				// Contrary, for handling photos programmatically, timestamps
				// (in UTC) are best.
				// Unfortunately, the EXIF specification prior to version 2.31
				// did not consider timezone information and only defined
				// tag #9003 "DateTimeOriginal" which uses the string format
				// "YYYY-MM-DD hh:mm:ss" _without_ timezone information.
				// Moreover, the specification left open, if this string should
				// represent a "wall time" relative to the local timezone of the
				// location where the photo has been taken or a UTC-based time.
				// As most cameras for still photography have just a dumb
				// timezone-unaware clock, they simply store that time.
				// This time is most probably the "wall time" in the local
				// timezone assuming that the owner of the camera has set the
				// correct time.
				// For videos the situation is a little bit different.
				// Some video cameras store creation time in local time while
				// others use UTC and it's often impossible to tell, especially
				// since the metadata extractors are not consistent either.
				// Since 2016 and EXIF 2.31 the situation has improved.
				// Next to the old tag "DateTimeOriginal" EXIF 2.31 also includes
				// GPS datetime information and GPS time offset.
				// On top, there is XMP which has been created by Adobe but is
				// now an ISO standard and always included timezone information
				// as part of the specification.
				//
				// Here, we rely here on a simple filetype-based heuristics and,
				// for a timestamp we suspect to be in UTC, we convert it to the
				// application's default timezone.
				// All other timestamps are not altered, but used "as is":
				//
				//   i) Either the meta-data extractor was able to properly
				//      extract a timezone information (good case), or
				//  ii) the meta-data extractor returned a \DateTime object which
				//      uses the application's default timezone due to the EXIF
				//      date lacking an explicit timezone (bad case).
				//
				// In the "bad case", the shown "wall time" relative to the
				// application's default timezone matches the EXIF time.
				// This approach implicitly assumes that the beholder of the photo
				// in front of the GUI uses the same timezone as the backend
				// and thus sees the correct "wall time" which is consistent to
				// content of the photo.
				//
				// Other possible approaches would include deriving the original
				// timezone from the file name or from other objects in the same
				// album, as well as extracting the timezone from the location
				// data if present.
				// The latter is what the "big players" like Google Photo or
				// Apple do.
				// TODO: Implement timezone derivation from location data.
				// See [this StackOverflow answer](https://stackoverflow.com/a/16086964/2690527)
				// for a fairly comprehensive overview of available options.
				// The [Geo-Timezone PHP Library](https://github.com/minube/geo-timezone)
				// seems to be the most accurate one and does not depend on an
				// external web-service.
				// Unfortunately, it is not an simple PHP library which can be
				// pulled in as a Composer dependencies, but requires a binary
				// PHP extension (`geos.so`).
				//
				// **Notes about b):**
				//
				// With respect to the beholder, b) is irrelevant.
				// However, please be aware that there is not necessarily a single
				// server timezone, but actually three.
				// The timezone of the server OS, the configured timezone of the
				// PHP application and the timezone of SQL connection to the SQL
				// server.
				// Those three timezone are not necessarily identical, especially
				// not, if the Lychee application and the SQL server are running
				// on different machines.
				// {@link App\Models\PatchedBaseModel} takes care that
				// all timestamps are (de-)hydrated as UTC timestamps.
				// Moreover, {@link App\Models\Photo} ensures that the original
				// timezone information of the datetime when the photo has been
				// taken is stored.
				//
				// **Notes about c):**
				//
				// The datetime is sent from the web backend to the client using
				// the JSON (aka ISO 8601) format incl. the correct time-offset
				// (e.g. 20210519T211643+02:00).
				// On top, the original timezone is sent to the client as
				// the string attribute `taken_at_orig_tz` which either is
				//
				//  - a named timezone like "Europe/Paris" (most accurate),
				//  - a timezone abbreviation like "CEST" (central european summer
				//    time, less accurate), or
				//  - a time offset like "+02:00" (least accurate),
				//
				// whatever the metadata extractor was able to extract from the
				// media file.
				// In theory, this give the GUI to show the datetime of creation
				// either
				//
				//  a) relative to the original timezone (probably the most
				//     useful option),
				//  b) relative to UTC, or
				//  c) relative to the beholder's own, local timezone.
				//
				// Note 1: At the moment, the "original timezone" typically is not
				//         the "true" original timezone, but the configured
				//         default timezone of the PHP application (see notes
				//         about a).
				// Note 2: We do not set the the attribute `taken_at_orig_tz`
				//         here.
				//         This is the responsibility of {@link App\Models\Photo}.
				//         At the layer of the "business logic" we only use
				//         the attribute `taken_at` which extends
				//         \DateTimeInterface and stores the timezone.
				if ($file->isSupportedVideo()) {
					$locals = strtolower(Configs::get_value('local_takestamp_video_formats', ''));
					if (!in_array(strtolower($extension), explode('|', $locals), true)) {
						// This is a video format where we expect the takestamp
						// to be provided in UTC.
						if ($taken_at->getTimezone()->getName() === date_default_timezone_get()) {
							// Most likely the time zone info was missing so the
							// system default was used instead, which is wrong,
							// because the recording time is actually in UTC.
							// This will trigger, e.g., for mp4 files with the
							// Exiftool extractor.
							// We recreate the recording time as a UTC timestamp
							// and _then_ change the timezone to the application's
							// default timezone.
							// Note: This assumes that the application's default
							// timezone is the same as the timezone of the
							// location where the video has been recorded and that
							// the beholder (of the video) expects to observe
							// that timezone.
							$taken_at = new Carbon(
								$taken_at->format('Y-m-d H:i:s'),
								new \DateTimeZone('UTC')
							);
							$taken_at->setTimezone(new \DateTimeZone(date_default_timezone_get()));
						} elseif ($taken_at->getTimezone()->getName() === 'Z') {
							// This one is correctly in Zulu (UTC).
							// We change the timezone to the application's default
							// timezone and convert the time.
							// Note: This assumes that the application's default
							// timezone is the same as the timezone of the
							// location where the video has been recorded and that
							// the beholder (of the video) expects to observe
							// that timezone.
							$taken_at->setTimezone(new \DateTimeZone(date_default_timezone_get()));
						}
						// In the remaining cases the timezone information was
						// extracted and the recording time is assumed exhibit
						// to original timezone of the location where the video
						// has been recorded.
						// So we don't need to do anything.
						//
						// The only known example are the mov files from Apple
						// devices; the time zone will be formatted as "+01:00"
						// so neither of the two conditions above should trigger.
					}
				}
				$metadata['taken_at'] = $taken_at;
			} catch (InvalidTimeZoneException|InvalidFormatException $e) {
				throw new MediaFileOperationException('Could not even extract date/time from EXIF data', $e);
			}
		} else {
			$metadata['taken_at'] = null;
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

		if ($file->isSupportedImage() || $file->isAcceptedRaw()) {
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

		// Decode location data, it can be longer than is acceptable for DB that's the reason for substr
		// but only if return value is not null (= function has been disabled)
		try {
			$metadata['location'] = Geodecoder::decodeLocation($metadata['latitude'], $metadata['longitude']);
			if ($metadata['location'] !== null) {
				$metadata['location'] = substr($metadata['location'], 0, 255);
			}
		} catch (ExternalComponentFailedException $e) {
			report($e);
			$metadata['location'] = null;
		}

		return $metadata;
	}
}
