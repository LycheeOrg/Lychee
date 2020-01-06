<?php

namespace App\Metadata;

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
		$reader = null;

		if (strpos($type, 'video') !== 0) {
			// It's a photo
			if (Configs::hasExiftool() == true) {
				// reader with Exiftool adapter
				$reader = Reader::factory(Reader::TYPE_EXIFTOOL);
			} elseif (strpos($type, 'video') !== 0) {
				// Use Php native tools
				$reader = Reader::factory(Reader::TYPE_NATIVE);
			}
		} else {
			// Let's try to use FFmpeg; if not available, let's try Exiftool
			if (Configs::hasFFmpeg() == true) {
				// It's a video -> use FFProbe
				$reader = Reader::factory(Reader::TYPE_FFPROBE);
			} elseif (Configs::hasExiftool() == true) {
				// reader with Exiftool adapter
				$reader = Reader::factory(Reader::TYPE_EXIFTOOL);
			} else {
				// Use Php native tools to extract at least MimeType and Filesize
				// For all other properties, it will not return anything
				$reader = Reader::factory(Reader::TYPE_NATIVE);
				Logs::notice(__METHOD__, __LINE__, 'FFmpeg and Exiftool not being available; Extraction of metadata limited to mime type and file size.');
			}
		}

		$exif = $reader->read($filename);
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
		$metadata['takestamp'] = ($exif->getCreationDate() !== false) ? $exif->getCreationDate()->format('Y-m-d H:i:s') : null;
		$metadata['lens'] = ($exif->getLens() !== false) ? $exif->getLens() : '';
		$metadata['tags'] = ($exif->getKeywords() !== false) ? (is_array($exif->getKeywords()) ? implode(',', $exif->getKeywords()) : $exif->getKeywords()) : '';
		$metadata['latitude'] = ($exif->getLatitude() !== false) ? $exif->getLatitude() : null;
		$metadata['longitude'] = ($exif->getLongitude() !== false) ? $exif->getLongitude() : null;
		$metadata['altitude'] = ($exif->getAltitude() !== false) ? $exif->getAltitude() : null;
		$metadata['imgDirection'] = ($exif->getImgDirection() !== false) ? $exif->getImgDirection() : null;
		$metadata['size'] = ($exif->getFileSize() !== false) ? $exif->getFileSize() : 0;
		$metadata['livePhotoContentID'] = ($exif->getContentIdentifier() !== false) ? $exif->getContentIdentifier() : null;
		$metadata['MicroVideoOffset'] = ($exif->getMicroVideoOffset() !== false) ? $exif->getMicroVideoOffset() : null;

		// We need to make sure, takestamp is between '1970-01-01 00:00:01' UTC to '2038-01-19 03:14:07' UTC.
		// We set value to null in case we're out of bounds
		if ($metadata['takestamp'] !== null) {
			$min_date = new \DateTime('1970-01-01 00:00:01', new \DateTimeZone('UTC'));
			$max_date = new \DateTime('2038-01-19 03:14:07', new \DateTimeZone('UTC'));
			$takestamp = new \DateTime($metadata['takestamp']);
			if ($takestamp < $min_date || $takestamp > $max_date) {
				$metadata['takestamp'] = null;
				Logs::notice(__METHOD__, __LINE__, 'Takestamp (' . $takestamp->format('Y-m-d H:i:s') . ') out of bounds (needs to be between 1970-01-01 00:00:01 and 2038-01-19 03:14:07)');
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

		if ((strpos($type, 'video') !== 0)) {
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

		return $metadata;
	}
}
