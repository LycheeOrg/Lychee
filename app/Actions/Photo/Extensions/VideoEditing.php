<?php

namespace App\Actions\Photo\Extensions;

use App\Metadata\Extractor;
use App\ModelFunctions\MOVFormat;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Exception;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Helpers;
use Illuminate\Support\Facades\Storage;
use ImageOptimizer;

trait VideoEditing
{
	use Checks;

	/**
	 * @param Photo $photo
	 *
	 * @return string Path of the video frame
	 */
	public function extractVideoFrame(Photo $photo): string
	{
		if (!Configs::hasFFmpeg()) {
			Logs::notice(__METHOD__, __LINE__, 'Failed to extract snapshot: bad config: ' . Configs::hasFFmpeg());

			return '';
		}
		if ($photo->aperture === '') {
			$path = Storage::path('big/' . $photo->url);

			/* @var  Extractor $metadataExtractor */
			$metadataExtractor = resolve(Extractor::class);
			$info = $metadataExtractor->extract($path, 'video');
			$photo->aperture = $info['aperture'];
		}
		// we check again, just to be sure.
		if ($photo->aperture === '') {
			return '';
		}

		/**
		 * ! check if we can use path instead of this ugly thing.
		 */
		$ffmpeg = FFMpeg::create();
		/** @var Video */
		$video = $ffmpeg->open(Storage::path('big/' . $photo->url));
		if (
			!($tmp = tempnam(sys_get_temp_dir(), 'lychee')) ||
			!rename($tmp, $tmp . '.jpeg')
		) {
			Logs::notice(__METHOD__, __LINE__, 'Could not create a temporary file.');

			return '';
		}
		$tmp .= '.jpeg';
		Logs::notice(__METHOD__, __LINE__, 'Saving frame to ' . $tmp);

		try {
			/**
			 * ! check if we can use path instead of this ugly thing.
			 */
			$frame = $video->frame(TimeCode::fromSeconds($photo->aperture / 2));
			$frame->save($tmp);
		} catch (Exception $e) {
			Logs::notice(__METHOD__, __LINE__, 'Failed to extract snapshot from video ' . $tmp);
		}

		// check if the image has data
		$success = file_exists($tmp) ? (filesize($tmp) > 0) : false;

		if ($success) {
			// Optimize image
			if (Configs::get_value('lossless_optimization')) {
				ImageOptimizer::optimize($tmp);
			}
		} else {
			Logs::notice(__METHOD__, __LINE__, 'Failed to extract snapshot from video ' . $tmp);
			try {
				/**
				 * ! check if we can use path instead of this ugly thing.
				 */
				$frame = $video->frame(TimeCode::fromSeconds(0));
				$frame->save($tmp);
				$success = file_exists($tmp) ? (filesize($tmp) > 0) : false;
				if (!$success) {
					Logs::notice(__METHOD__, __LINE__, 'Fallback failed to extract snapshot from video ' . $tmp);
				} else {
					Logs::notice(__METHOD__, __LINE__, 'Fallback successful - snapshot from video ' . $tmp . ' at t=0 created.');
				}
			} catch (Exception $e) {
				Logs::notice(__METHOD__, __LINE__, 'Fallback failed to extract snapshot from video ' . $tmp);

				return '';
			}
		}

		return $tmp;
	}

	/**
	 * Extract the video part of the a Livephoto.
	 *
	 * @param Photo  $photo
	 * @param string $type
	 * @param int    $maxWidth
	 * @param int    $maxHeight
	 * @param string Path of the video frame
	 *
	 * @return bool
	 */
	public function extractVideo(Photo $photo, int $videoLengthBytes, string $frame_tmp = ''): bool
	{
		// We extract the video from the jpg file
		// Google Motion Photo: See here for details
		//

		if ($frame_tmp === '') {
			$filename = $photo->url;
		} else {
			$filename = $photo->thumbUrl;
		}

		$filename_video_mov = basename($filename, Helpers::getExtension($filename, false)) . '.mov';

		$uploadFolder = $this->folderPermission('big/');

		try {
			// 1. Extract the video part
			$fp = fopen($uploadFolder . $photo->url, 'r');
			$fp_video = tmpfile(); // use a temporary file, will be delted once closed

			// The MP4 file is located in the last bytes of the file
			fseek($fp, -1 * $videoLengthBytes, SEEK_END); // It needs to be negative
			$data = fread($fp, $videoLengthBytes);
			fwrite($fp_video, $data, $videoLengthBytes);

			// 2. Convert file from mp4 to mov, but keeping audio and video codec
			// This is needed to LivePhotosKit which only accepts mov files
			// Computation is fast, since codecs, resolution, framerate etc. remain unchanged

			/**
			 * ! check if we can use path instead of this ugly thing.
			 */
			$ffmpeg = FFMpeg::create();
			$video = $ffmpeg->open(stream_get_meta_data($fp_video)['uri']);
			$format = new MOVFormat();
			// Add additional parameter to extract the first video stream
			$format->setAdditionalParameters(['-map', '0:0']);
			$video->save($format, $uploadFolder . $filename_video_mov);

			// 3. Close files ($fp_video will be again deleted)
			fclose($fp);
			fclose($fp_video);

			// Save file path; Checksum calclation not needed since
			// we do not perform matching for Google Motion Photos (as for iOS Live Photos)
			$photo->livePhotoUrl = $filename_video_mov;
		} catch (Exception $exception) {
			Logs::error(__METHOD__, __LINE__, $exception->getMessage());

			return false;
		}

		return true;
	}
}
