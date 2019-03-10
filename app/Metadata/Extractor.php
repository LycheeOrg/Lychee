<?php

namespace App\Metadata;

use App\Logs;
use FFMpeg;

class Extractor
{
	/**
	 * Extracts metadata from an image file
	 *
	 * @param  string $filename
	 * @return array
	 */
	public function extract(string $filename, string $type): array
	{
		$metadata = [
			'type'        => '',
			'width'       => 0,
			'height'      => 0,
			'title'       => '',
			'description' => '',
			'orientation' => '',
			'iso'         => '',
			'aperture'    => '',
			'make'        => '',
			'model'       => '',
			'shutter'     => '',
			'focal'       => '',
			'takestamp'   => null,
			'lens'        => '',
			'tags'        => '',
			'position'    => '',
			'latitude'    => null,
			'longitude'   => null,
			'altitude'    => null
		];
		$imageInfo = [];
		if (strpos($type, 'video') !== 0) {
			$info = getimagesize($filename, $imageInfo);
			$metadata['type'] = $info['mime'];
			$metadata['width'] = $info[0];
			$metadata['height'] = $info[1];
		}
		else {
			try {
				$this->extractVideo($filename, $metadata);
			} catch (\Exception $exception) {
				Logs::error(__METHOD__, __LINE__, $exception->getMessage());
			}
			$metadata['type'] = $type;
		}

		// Size
		$size = filesize($filename) / 1024;
		if ($size >= 1024) {
			$metadata['size'] = round($size / 1024, 1).' MB';
		}
		else {
			$metadata['size'] = round($size, 1).' KB';
		}

		// IPTC Metadata
		// See https://www.iptc.org/std/IIM/4.2/specification/IIMV4.2.pdf for mapping
		if (isset($imageInfo['APP13'])) {
			$imageInfo = iptcparse($imageInfo['APP13']);
			if (is_array($imageInfo)) {
				// Title
				if (!empty($imageInfo['2#105'][0])) {
					$metadata['title'] = $imageInfo['2#105'][0];
				}
				else {
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
		}
		else {
			$exif = false;
		}

		// EXIF Metadata
		if ($exif !== false) {
			// Orientation
			if (isset($exif['Orientation'])) {
				$metadata['orientation'] = $exif['Orientation'];
			}
			else {
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
				$metadata['shutter'] = $exif['ExposureTime'].' s';
			}

			// Focal Length
			if (!empty($exif['FocalLength'])) {
				if (strpos($exif['FocalLength'], '/') !== false) {
					$temp = explode('/', $exif['FocalLength'], 2);
					$temp = $temp[0] / $temp[1];
					$temp = round($temp, 1);
					$metadata['focal'] = $temp.' mm';
				}
				else {
					$metadata['focal'] = $exif['FocalLength'].' mm';
				}
			}

			// Takestamp
			if (!empty($exif['DateTimeOriginal'])) {
				if ($exif['DateTimeOriginal'] == '0000:00:00 00:00:00') {
					$metadata['takestamp'] = null;
				}
				else {
					if (strtotime($exif['DateTimeOriginal']) == 0) {
						$metadata['takestamp'] = null;
					}
					else {
						$metadata['takestamp'] = date("Y-m-d H:i:s", strtotime($exif['DateTimeOriginal']));
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
		}

		return $metadata;
	}



	/**
	 * Returns the normalized coordinate from EXIF array
	 *
	 * @param array $coordinate
	 * @param string $ref
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
	 * Converts a `rational64u` to a float (`29451/625 => 47.1216`)
	 *
	 * @param  string $rational
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
		if ($parts[1] == 0)
			return (float) 0;

		return (float) $parts[0] / $parts[1];
	}



	/**
	 * Returns the altitude either above or below sea level
	 *
	 * @param  string $altitude
	 * @param  string $ref
	 * @return float
	 */
	private function getGPSAltitude(string $altitude, string $ref): float
	{
		$flip = ($ref == '1') ? -1 : 1;
		return $flip * $this->formattedToFloatGPS($altitude);
	}



	private function extractVideo(string $filename, array &$metadata)
	{
		$ffprobe = FFMpeg\FFProbe::create();

		$stream = $ffprobe->streams($filename)->videos()->first()->all();
		$format = $ffprobe->format($filename)->all();

		if (isset($stream['width'])) {
			$metadata['width'] = $stream['width'];
		}

		if (isset($stream['height'])) {
			$metadata['height'] = $stream['height'];
		}

		if (isset($stream['avg_frame_rate'])) {
			$framerate = explode('/', $stream['avg_frame_rate']);
			if (count($framerate) == 1) {
				$framerate = $framerate[0];
			} elseif (count($framerate) == 2 && $framerate[1] != 0) {
				$framerate = number_format($framerate[0] / $framerate[1], 3);
			}
			else {
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
				$metadata['takestamp'] = date("Y-m-d H:i:s", strtotime($format['tags']['creation_time']));
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
		}
	}
}
