<?php

namespace App\Metadata;

use App\Logs;
use FFMpeg;
use Exception;

class Extractor
{
	/**
	 * return bare array for info.
	 *
	 * @return array
	 */
	public function bare()
	{
		$metadata = [
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
			'size' => 0,
			'livePhotoContentID' => null,
			'livePhotoStillImageTime' => null,
			'MicroVideoOffset' => null,
		];

		return $metadata;
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
	 * @param  string mime type
	 *
	 * @return array
	 */
	public function extract(string $filename, string $type): array
	{

		$metadata = $this->extract_phpexif($filename, $type, true);

		// This section is only for debugging of new Solution
		// to be removed in the future
		$debug = true;
		if($debug) {

			$metadata_legacy = $this->extract_legacy($filename, $type);

			$error_msg = '';
			foreach ($metadata as $key => $value) {
				$match = true;
				// both are numbers
				if(gettype($metadata[$key])=='double' || gettype($metadata_legacy[$key])=='double') {
					// exiftool and php function have different precisions
					// difference needs to be small
					if(abs(floatval($metadata[$key])-floatval($metadata_legacy[$key]))>0.001) {
						$match = false;
					}
				} else {
					if(strval($metadata[$key])!==strval($metadata_legacy[$key])) {
						$match = false;
					}
				}
				if($match===false){
					Logs::notice(__METHOD__, __LINE__, 'Extracted EXIF data to not match: ' . $key . ' ' . $metadata[$key] . ' (' . gettype($metadata[$key]) . ') '. $metadata_legacy[$key] . ' (' . gettype($metadata_legacy[$key]) . ') ');
				}
			}
		}

		return $metadata;
	}

	/**
	 * Extracts metadata from an image file.
	 *
	 * @param string $filename
	 * @param string mime type
	 * @param bool force use of native extractor
	 * @return array
	 */
	public function extract_phpexif(string $filename, string $type, bool $force_native_extractor): array
	{
		$reader = null;
		$path_exiftool = exec('which exiftool');
		if(!(strpos($path_exiftool, 'exiftool')===false)){
			// reader with Exiftool adapter
			$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_EXIFTOOL);
		} else {
			// reader with Native adapter
			Logs::notice(__METHOD__, __LINE__, 'Exiftool not found - using php standard functions and FFMpeg (if available)');
			$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_NATIVE);
		}

		$exif = $reader->read($filename);
		$metadata = $this->bare();
		$metadata['type'] = ($exif->getMimeType()!==false) ? $exif->getMimeType() : '';
		$metadata['width'] = ($exif->getWidth()!==false) ? $exif->getWidth() : 0;
		$metadata['height'] = ($exif->getHeight()!==false) ? $exif->getHeight() : 0;
		$metadata['title'] = ($exif->getTitle()!==false) ? $exif->getTitle() : '';
		$metadata['description'] = ($exif->getDescription()!==false) ? $exif->getDescription() : '';
		$metadata['orientation'] = ($exif->getOrientation()!==false) ? $exif->getOrientation() : '';
		$metadata['iso'] = ($exif->getIso()!==false) ? $exif->getIso() : '';
		$metadata['make'] = ($exif->getMake()!==false) ? $exif->getMake() : '';
		$metadata['model'] = ($exif->getCamera()!==false) ? $exif->getCamera() : '';
		$metadata['shutter'] = ($exif->getExposure()!==false) ? $exif->getExposure() : '';
		$metadata['takestamp'] = ($exif->getCreationDate()!==false) ? $exif->getCreationDate()->format('Y-m-d H:i:s') : null;
		$metadata['lens'] = ($exif->getLens()!==false) ? $exif->getLens() : '';
		$metadata['tags'] = ($exif->getKeywords()!==false) ? ( is_array($exif->getKeywords()) ? implode(',', $exif->getKeywords()) : $exif->getKeywords() ): '';
		$metadata['latitude'] = ($exif->getLatitude()!==false) ? $exif->getLatitude() : null;
		$metadata['longitude'] = ($exif->getLongitude()!==false) ? $exif->getLongitude() : null;
		$metadata['altitude'] = ($exif->getAltitude()!==false) ? $exif->getAltitude() : null;
		$metadata['imgDirection'] = ($exif->getImgDirection()!==false) ? $exif->getImgDirection() : null;
		$metadata['size'] = ($exif->getFileSize()!==false) ? $exif->getFileSize() : 0;
		$metadata['livePhotoContentID'] = ($exif->getContentIdentifier()!==false) ? $exif->getContentIdentifier() : null;
		$metadata['MicroVideoOffset'] = ($exif->getMicroVideoOffset()!==false) ? $exif->getMicroVideoOffset() : null;

		if ((strpos($type, 'video') !== 0)) {
			$metadata['aperture'] = ($exif->getAperture()!==false) ? $exif->getAperture() : '';
			$metadata['focal'] = ($exif->getFocalLength()!==false) ? $exif->getFocalLength() : '';
			if ($metadata['focal']!=='') {
				$metadata['focal'] = round($metadata['focal']) . ' mm';
			}
		} else {
			// Video -> reuse fields
			$metadata['aperture'] = ($exif->getDuration()!==false) ? $exif->getDuration() : '';
			$metadata['focal'] = ($exif->getFramerate()!==false) ? $exif->getFramerate() : '';
		}

		if ($metadata['title']=='') {
			$metadata['title'] = ($exif->getHeadline()!==false) ? $exif->getHeadline() : '';
		}

		if ($metadata['shutter']!=='') {
			$metadata['shutter'] = $metadata['shutter'] . ' s';
		}
		if ($metadata['size']>0) {
			$metadata['size'] = $metadata['size'] / 1024;
			if ($metadata['size'] >= 1024) {
				$metadata['size'] = round($metadata['size'] / 1024, 1) . ' MB';
			} else {
				$metadata['size'] = round($metadata['size'], 1) . ' KB';
			}
		}



		return $metadata;
	}

	/**
	 * Extracts metadata from an image file.
	 *
	 * @param string $filename
	 * @param  string mime type
	 *
	 * @return array
	 */
	public function extract_legacy(string $filename, string $type): array
	{
		$metadata = $this->bare();

		$imageInfo = [];
		if (strpos($type, 'video') !== 0) {
			$info = getimagesize($filename, $imageInfo);
			$metadata['type'] = $info['mime'];
			$metadata['width'] = $info[0];
			$metadata['height'] = $info[1];
		} else {
			try {
				$this->extractVideo($filename, $metadata);
			} catch (Exception $exception) {
				Logs::error(__METHOD__, __LINE__, $exception->getMessage());
			}
			$metadata['type'] = $type;
		}

		// Size
		$this->size($metadata, $filename);

		// IPTC Metadata
		// See https://www.iptc.org/std/IIM/4.2/specification/IIMV4.2.pdf for mapping
		if (isset($imageInfo['APP13'])) {
			$imageInfo = iptcparse($imageInfo['APP13']);
			if (is_array($imageInfo)) {
				// Title
				if (!empty($imageInfo['2#105'][0])) {
					$metadata['title'] = $imageInfo['2#105'][0];
				} else {
					if (!empty($imageInfo['2#005'][0])) {
						$metadata['title'] = $imageInfo['2#005'][0];
					}
				}

				// Description
				if (!empty($imageInfo['2#120'][0])) {
					$metadata['description'] = $imageInfo['2#120'][0];
				}

				// Tags
				if (!empty($imageInfo['2#025'])) {
					$metadata['tags'] = implode(',', $imageInfo['2#025']);
				}

				// Position
				$fields = array();
				if (!empty($imageInfo['2#090'])) {
					$fields[] = trim($imageInfo['2#090'][0]);
				}
				if (!empty($imageInfo['2#092'])) {
					$fields[] = trim($imageInfo['2#092'][0]);
				}
				if (!empty($imageInfo['2#095'])) {
					$fields[] = trim($imageInfo['2#095'][0]);
				}
				if (!empty($imageInfo['2#101'])) {
					$fields[] = trim($imageInfo['2#101'][0]);
				}

				if (!empty($fields)) {
					$metadata['position'] = implode(', ', $fields);
				}
			}
		}

		// Read EXIF
		if ($metadata['type'] === 'image/jpeg') {
			$exif = @exif_read_data($filename, 'EXIF', false, false);
		} else {
			$exif = false;
		}

		// EXIF Metadata
		if ($exif !== false) {
			// Orientation
			if (isset($exif['Orientation'])) {
				$metadata['orientation'] = $exif['Orientation'];
			} else {
				if (isset($exif['IFD0']['Orientation'])) {
					$metadata['orientation'] = $exif['IFD0']['Orientation'];
				}
			}

			// ISO
			if (!empty($exif['ISOSpeedRatings'])) {
				$metadata['iso'] = $exif['ISOSpeedRatings'];
			}

			// Aperture
			if (!empty($exif['COMPUTED']['ApertureFNumber'])) {
				$metadata['aperture'] = $exif['COMPUTED']['ApertureFNumber'];
			}

			// Make
			if (!empty($exif['Make'])) {
				$metadata['make'] = trim($exif['Make']);
			}

			// Model
			if (!empty($exif['Model'])) {
				$metadata['model'] = trim($exif['Model']);
			}

			// Exposure
			if (!empty($exif['ExposureTime'])) {
				$metadata['shutter'] = $exif['ExposureTime'] . ' s';
			}

			// Focal Length
			if (!empty($exif['FocalLength'])) {
				if (strpos($exif['FocalLength'], '/') !== false) {
					$temp = explode('/', $exif['FocalLength'], 2);
					$temp = $temp[0] / $temp[1];
					$temp = round($temp, 1);
					$metadata['focal'] = $temp . ' mm';
				} else {
					$metadata['focal'] = $exif['FocalLength'] . ' mm';
				}
			}

			// Takestamp
			if (!empty($exif['DateTimeOriginal'])) {
				if ($exif['DateTimeOriginal'] == '0000:00:00 00:00:00') {
					$metadata['takestamp'] = null;
				} else {
					if (strtotime($exif['DateTimeOriginal']) == 0) {
						$metadata['takestamp'] = null;
					} else {
						$metadata['takestamp'] = date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']));
					}
				}
			}

			if (!empty($exif['LensInfo'])) {
				$metadata['lens'] = trim($exif['LensInfo']);
			}
			// Lens field from Lightroom
			if ($metadata['lens'] == '' && !empty($exif['UndefinedTag:0xA434'])) {
				$metadata['lens'] = trim($exif['UndefinedTag:0xA434']);
			}
			if ($metadata['lens'] == '' && !empty($exif['LensType'])) {
				$metadata['lens'] = trim($exif['LensType']);
			}

			// Deal with GPS coordinates
			if (!empty($exif['GPSLatitude']) && !empty($exif['GPSLatitudeRef'])) {
				$metadata['latitude'] = $this->getGPSCoordinate($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
			}
			if (!empty($exif['GPSLongitude']) && !empty($exif['GPSLongitudeRef'])) {
				$metadata['longitude'] = $this->getGPSCoordinate($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
			}
			if (!empty($exif['GPSAltitude']) && !empty($exif['GPSAltitudeRef'])) {
				$metadata['altitude'] = $this->getGPSAltitude($exif['GPSAltitude'], $exif['GPSAltitudeRef']);
			}
			if (!empty($exif['GPSImgDirection']) && !empty($exif['GPSImgDirectionRef'])) {
				$metadata['imgDirection'] = $this->getGPSImgDirection($exif['GPSImgDirection'], $exif['GPSImgDirectionRef']);
			}
		}

		$this->validate($metadata);

		return $metadata;
	}

	/**
	 * Reset field value to empty string if the data is binary (invalid UTF-8 chars).
	 *
	 * @param array $metadata
	 */
	public function validate(array &$metadata)
	{
		foreach ($metadata as $k => $v) {
			if (!mb_check_encoding($v)) {
				// @codeCoverageIgnoreStart
				$metadata[$k] = '';
				// @codeCoverageIgnoreEnd
			}
		}
	}

	/**
	 * Returns the normalized coordinate from EXIF array.
	 *
	 * @param array  $coordinate
	 * @param string $ref
	 *
	 * @return float Normalized coordinate as float number (degrees)
	 */
	private function getGPSCoordinate(array $coordinate, string $ref): float
	{
		$degrees = count($coordinate) > 0 ? $this->formattedToFloatGPS($coordinate[0]) : 0;
		$minutes = count($coordinate) > 1 ? $this->formattedToFloatGPS($coordinate[1]) : 0;
		$seconds = count($coordinate) > 2 ? $this->formattedToFloatGPS($coordinate[2]) : 0;

		$flip = ($ref == 'W' || $ref == 'S') ? -1 : 1;

		return $flip * ($degrees + (float) $minutes / 60 + (float) $seconds / 3600);
	}

	/**
	 * Converts a `rational64u` to a float (`29451/625 => 47.1216`).
	 *
	 * @param string $rational
	 *
	 * @return float
	 */
	private function formattedToFloatGPS(string $rational): float
	{
		$parts = explode('/', $rational, 2);

		if (count($parts) <= 0) {
			return 0.0;
		}
		if (count($parts) == 1) {
			return (float) $parts[0];
		}
		// case part[1] is 0, div by 0 is forbidden.
		if ($parts[1] == 0) {
			return (float) 0;
		}

		return (float) $parts[0] / $parts[1];
	}

	/**
	 * Returns the altitude either above or below sea level.
	 *
	 * @param string $altitude
	 * @param string $ref
	 *
	 * @return float
	 */
	private function getGPSAltitude(string $altitude, string $ref): float
	{
		$flip = ($ref == '1' || $ref == "\u{0001}") ? -1 : 1;

		return $flip * $this->formattedToFloatGPS($altitude);
	}

	/**
	 * Returns the image direction.
	 *
	 * @param string direction
	 * @param string $ref
	 *
	 * @return float
	 */
	private function getGPSImgDirection(string $direction, string $ref): float
	{
		// Simplification: we ignore the difference between magnetic and true north

		return $this->formattedToFloatGPS($direction);
	}

	/**
	 * Converts results of ISO6709 parsing
	 * to decimal format for latitude and longitude
	 * See https://github.com/seanson/python-iso6709.git.
	 *
	 * @param string sign
	 * @param string degrees
	 * @param string minutes
	 * @param string seconds
	 * @param string fraction
	 *
	 * @return float
	 */
	private function convertDMStoDecimal(string $sign, string $degrees, string $minutes, string $seconds, string $fraction): float
	{
		if ($fraction !== '') {
			if ($seconds !== '') {
				$seconds = $seconds . $fraction;
			} elseif ($minutes !== '') {
				$minutes = $minutes . $fraction;
			} else {
				$degrees = $degrees . $fraction;
			}
		}
		$decimal = floatval($degrees) + floatval($minutes) / 60.0 + floatval($seconds) / 3600.0;
		if ($sign == '-') {
			$decimal = -1.0 * $decimal;
		}

		return $decimal;
	}

	/**
	 * Returns the latitude, longitude and altitude
	 * of a GPS coordiante formattet with ISO6709
	 * See https://github.com/seanson/python-iso6709.git.
	 *
	 * @param string val_ISO6709
	 *
	 * @return array
	 */
	private function readISO6709(string $val_ISO6709): array
	{
		$return = [
			'latitude' => null,
			'long2ip' => null,
			'altitude' => null,
		];
		$matches = [];

		// Adjustment compared to https://github.com/seanson/python-iso6709.git
		// Altitude have format +XX.XXXX -> Adjustment for decimal
		preg_match('/^(?<lat_sign>\+|-)(?<lat_degrees>[0,1]?\d{2})(?<lat_minutes>\d{2}?)?(?<lat_seconds>\d{2}?)?(?<lat_fraction>\.\d+)?(?<lng_sign>\+|-)(?<lng_degrees>[0,1]?\d{2})(?<lng_minutes>\d{2}?)?(?<lng_seconds>\d{2}?)?(?<lng_fraction>\.\d+)?(?<alt>[\+\-][0-9]\d*(\.\d+)?)?\/$/', $val_ISO6709, $matches);
		$return['latitude'] = $this->convertDMStoDecimal($matches['lat_sign'], $matches['lat_degrees'], $matches['lat_minutes'], $matches['lat_seconds'], $matches['lat_fraction']);
		$return['longitude'] = $this->convertDMStoDecimal($matches['lng_sign'], $matches['lng_degrees'], $matches['lng_minutes'], $matches['lng_seconds'], $matches['lng_fraction']);
		if (isset($matches['alt'])) {
			$return['altitude'] = doubleval($matches['alt']);
		}

		return $return;
	}

	/**
	 * @param string $filename
	 * @param array  $metadata
	 */
	private function extractVideo(string $filename, array &$metadata)
	{
		$path_ffmpeg = exec('which ffmpeg');
		$path_ffprobe = exec('which ffprobe');
		$ffprobe = FFMpeg\FFProbe::create(array(
									'ffmpeg.binaries'  => $path_ffmpeg,
									'ffprobe.binaries' => $path_ffprobe,
							));

		$stream = $ffprobe->streams($filename)->videos()->first()->all();
		$format = $ffprobe->format($filename)->all();

		if (isset($stream['width'])) {
			$metadata['width'] = $stream['width'];
		}

		if (isset($stream['height'])) {
			$metadata['height'] = $stream['height'];
		}

		if (isset($stream['tags']) && isset($stream['tags']['rotate']) && ($stream['tags']['rotate'] === '90' || $stream['tags']['rotate'] === '270')) {
			$tmp = $metadata['width'];
			$metadata['width'] = $metadata['height'];
			$metadata['height'] = $tmp;
		}

		if (isset($stream['avg_frame_rate'])) {
			$framerate = explode('/', $stream['avg_frame_rate']);
			if (count($framerate) == 1) {
				$framerate = $framerate[0];
			} elseif (count($framerate) == 2 && $framerate[1] != 0) {
				$framerate = number_format($framerate[0] / $framerate[1], 3);
			} else {
				$framerate = '';
			}
			if ($framerate !== '') {
				$metadata['focal'] = $framerate;
			}
		}

		if (isset($format['duration'])) {
			$metadata['aperture'] = number_format($format['duration'], 3);
		}

		if (isset($format['tags'])) {
			if (isset($format['tags']['creation_time']) && strtotime($format['tags']['creation_time']) !== 0) {
				$metadata['takestamp'] = date('Y-m-d H:i:s', strtotime($format['tags']['creation_time']));
			}

			if (isset($format['tags']['location'])) {
				$matches = [];
				preg_match('/^([+-][0-9\.]+)([+-][0-9\.]+)\/$/', $format['tags']['location'], $matches);
				if (count($matches) == 3 &&
					!preg_match('/^\+0+\.0+$/', $matches[1]) &&
					!preg_match('/^\+0+\.0+$/', $matches[2])) {
					$metadata['latitude'] = $matches[1];
					$metadata['longitude'] = $matches[2];
				}
			}

			// QuickTime File Format defines several additional metadata
			// Source: https://developer.apple.com/library/archive/documentation/QuickTime/QTFF/Metadata/Metadata.html

			// Special case: iPhones write into tags->creation_time the creation time of the file
			// -> When converting the video from HEVC (iOS Video format) to MOV, the creation_time
			// is the time when the mov file was created, not when the video was shot (fixed in iOS12)
			// (see e.g. https://michaelkummer.com/tech/apple/photos-videos-wrong-date/ (for the symptom)
			// Solution: Use com.apple.quicktime.creationdate which is the true creation date of the video
			if (isset($format['tags']['com.apple.quicktime.creationdate'])) {
				$metadata['takestamp'] = date('Y-m-d H:i:s', strtotime($format['tags']['com.apple.quicktime.creationdate']));
			}

			if (isset($format['tags']['com.apple.quicktime.description'])) {
				$metadata['description'] = $format['tags']['com.apple.quicktime.description'];
			}

			if (isset($format['tags']['com.apple.quicktime.title'])) {
				$metadata['title'] = $format['tags']['com.apple.quicktime.title'];
			}

			if (isset($format['tags']['com.apple.quicktime.keywords'])) {
				$metadata['tags'] = implode(',', $format['tags']['com.apple.quicktime.keywords']);
			}

			if (isset($format['tags']['com.apple.quicktime.location.ISO6709'])) {
				$location_data = $this->readISO6709($format['tags']['com.apple.quicktime.location.ISO6709']);
				$metadata['latitude'] = $location_data['latitude'];
				$metadata['longitude'] = $location_data['longitude'];
				$metadata['altitude'] = $location_data['altitude'];
			}

			// Not documented, but available on iPhone videos
			if (isset($format['tags']['com.apple.quicktime.make'])) {
				$metadata['make'] = $format['tags']['com.apple.quicktime.make'];
			}

			// Not documented, but available on iPhone videos
			if (isset($format['tags']['com.apple.quicktime.model'])) {
				$metadata['model'] = $format['tags']['com.apple.quicktime.model'];
			}
		}
	}
}
