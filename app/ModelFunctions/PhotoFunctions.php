<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use App\Album;
use App\Configs;
use App\Image\ImageHandlerInterface;
use App\Logs;
use App\Metadata\Extractor;
use App\Photo;
use App\Response;
use Exception;
use FFMpeg;
use Illuminate\Database\QueryException;
use Storage;

class PhotoFunctions
{
	/**
	 * @var Extractor
	 */
	private $metadataExtractor;

	/**
	 * @var ImageHandlerInterface
	 */
	private $imageHandler;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @var array
	 */
	public $validTypes = [
		IMAGETYPE_JPEG,
		IMAGETYPE_GIF,
		IMAGETYPE_PNG,
	];

	/**
	 * @var array
	 */
	public $validVideoTypes = [
		'video/mp4',
		'video/mpeg',
		'video/ogg',
		'video/webm',
		'video/quicktime',
		'video/x-ms-asf', // wmv file
		'video/x-msvideo', // Avi
	];

	/**
	 * @var array
	 */
	public $validExtensions = [
		'.jpg',
		'.jpeg',
		'.png',
		'.gif',
		'.ogv',
		'.mp4',
		'.mpg',
		'.webm',
		'.mov',
		'.avi',
		'.wmv',
	];

	/**
	 * PhotoFunctions constructor.
	 *
	 * @param Extractor             $metadataExtractor
	 * @param ImageHandlerInterface $imageHandler
	 * @param SessionFunctions      $sessionFunctions
	 */
	public function __construct(Extractor $metadataExtractor, ImageHandlerInterface $imageHandler, SessionFunctions $sessionFunctions)
	{
		$this->metadataExtractor = $metadataExtractor;
		$this->imageHandler = $imageHandler;
		$this->sessionFunctions = $sessionFunctions;
	}

	/**
	 * @param Photo $photo
	 *
	 * @return string Path of the video frame
	 */
	public function extractVideoFrame(Photo $photo): string
	{
		if ($photo->aperture === '') {
			return '';
		}

		$ffmpeg = FFMpeg\FFMpeg::create();
		$video = $ffmpeg->open(Storage::path('big/' . $photo->url));
		$frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($photo->aperture / 2));

		$tmp = tempnam(sys_get_temp_dir(), 'lychee');
		Logs::notice(__METHOD__, __LINE__, 'Saving frame to ' . $tmp);
		$frame->save($tmp);

		return $tmp;
	}

	/**
	 * Create thumbnail for a picture.
	 *
	 * @param Photo $photo
	 * @param string Path of the video frame
	 *
	 * @return bool returns true when successful
	 */
	public function createThumb(Photo $photo, string $frame_tmp = '')
	{
		Logs::notice(__METHOD__, __LINE__, 'Photo URL is ' . $photo->url);

		$src = ($frame_tmp === '') ? Storage::path('big/' . $photo->url) : $frame_tmp;
		$photoName = explode('.', $photo->url);
		$this->imageHandler->crop(
			$src,
			Storage::path('thumb/' . $photoName[0] . '.jpeg'),
			200,
			200
		);

		if (Configs::get_value('thumb_2x') === '1' &&
			$photo->width >= 400 && $photo->height >= 400) {
			// Retina thumbs
			$this->imageHandler->crop(
				$src,
				Storage::path('thumb/' . $photoName[0] . '@2x.jpeg'),
				400,
				400
			);
			$photo->thumb2x = 1;
		} else {
			$photo->thumb2x = 0;
		}

		return true;
	}

	/**
	 * Returns 'photo' if it is a photo
	 * Returns 'video' if it is a video
	 * Returns 'raw' if it is an accepted file (we only check extensions)
	 * Returns 'error message' if it is something else.
	 *
	 * @param $file
	 * @param $extension
	 *
	 * @return string
	 */
	private function file_type($file, string $extension)
	{
		// check raw files
		if (in_array(strtolower($extension), explode('|', Configs::get_value('raw_formats', '')), true)) {
			return 'raw';
		}

		if (!in_array(strtolower($extension), $this->validExtensions, true)) {
			$mimeType = $file['type'];
			if (!in_array($mimeType, $this->validVideoTypes, true)) {
				// let's check for the mimetype
				// maybe we don't have a photo
				if (!function_exists('exif_imagetype')) {
					Logs::error(__METHOD__, __LINE__,
						'EXIF library not loaded. Make sure exif is enabled in php.ini');

					return 'EXIF library not loaded on the server!';
				}

				$type = @exif_imagetype($file['tmp_name']);
				if (!in_array($type, $this->validTypes, true)) {
					Logs::error(__METHOD__, __LINE__, 'Photo type not supported');

					return 'Photo type not supported!';
				}
				// we have maybe a raw file
				Logs::error(__METHOD__, __LINE__, 'Photo format not supported');

				return 'Photo format not supported!';
			}
			// we have a video
			return 'video';
		}
		// we have a normal photo
		return 'photo';
	}

	/**
	 * Add new photo(s) to the database.
	 * Exits on error.
	 *
	 * @param array $file
	 * @param int   $albumID_in
	 * @param bool  $delete_imported
	 *
	 * @return string|false ID of the added photo
	 */
	public function add(array $file, $albumID_in = 0, $delete_imported = false)
	{
		// Check permissions
		if (Helpers::hasPermissions(Storage::path('')) === false ||
			Helpers::hasPermissions(Storage::path('big/')) === false ||
			Helpers::hasPermissions(Storage::path('medium/')) === false ||
			Helpers::hasPermissions(Storage::path('small/')) === false ||
			Helpers::hasPermissions(Storage::path('thumb/')) === false ||
			Helpers::hasPermissions(Storage::path('import/')) === false
	) {
			Logs::error(__METHOD__, __LINE__, 'An upload-folder is missing or not readable and writable');

			return Response::error('An upload-folder is missing or not readable and writable!');
		}

		switch ($albumID_in) {
			// s for public (share)
			case 's':
				$public = 1;
				$star = 0;
				$albumID = null;
				break;

			// f for starred (fav)
			case 'f':
				$star = 1;
				$public = 0;
				$albumID = null;
				break;

			// r for recent
			// 0 for unsorted
			case '0':
			case 'r':
				$public = 0;
				$star = 0;
				$albumID = null;
				break;

			default:
				$star = 0;
				$public = 0;
				$albumID = $albumID_in;
				break;
		}

		// Verify extension
		$extension = Helpers::getExtension($file['name'], false);
		$mimeType = $file['type'];
		$kind = $this->file_type($file, $extension);

		if ($kind != 'photo' && $kind != 'video' && $kind != 'raw') {
			return Response::error($kind);
		}

		// Generate id
		$photo = new Photo();
		$photo->id = Helpers::generateID();

		// Set paths
		$tmp_name = $file['tmp_name'];
		$photo_name = md5(microtime()) . $extension;

		$path_prefix = $kind != 'raw' ? 'big/' : 'raw/';
		$path = Storage::path($path_prefix . $photo_name);

		// Calculate checksum
		$checksum = sha1_file($tmp_name);
		if ($checksum === false) {
			Logs::error(__METHOD__, __LINE__, 'Could not calculate checksum for photo');

			return Response::error('Could not calculate checksum for photo!');
		}
		$photo->checksum = $checksum;
		$exists = $photo->isDuplicate($checksum);

		// double check that
		if ($exists !== false) {
			$photo_name = $exists->url;
			$path = Storage::path($path_prefix . $exists->url);
			$photo->thumbUrl = $exists->thumbUrl;
			$photo->thumb2x = $exists->thumb2x;
			$photo->medium = $exists->medium;
			$photo->medium2x = $exists->medium2x;
			$photo->small = $exists->small;
			$photo->small2x = $exists->small2x;
			$photo->livePhotoUrl = $exists->livePhotoUrl;
			$photo->livePhotoChecksum = $exists->livePhotoChecksum;
			$photo->checksum = $exists->checksum;
			$exists = true;
		}

		if ($exists === false) {
			// Import if not uploaded via web
			if (!is_uploaded_file($tmp_name)) {
				// TODO: use the storage facade here
				if (!@copy($tmp_name, $path)) {
					Logs::error(__METHOD__, __LINE__, 'Could not copy photo to uploads');

					return Response::error('Could not copy photo to uploads!');
				} elseif ($delete_imported) {
					@unlink($tmp_name);
				}
			} else {
				// TODO: use the storage facade here
				if (!@move_uploaded_file($tmp_name, $path)) {
					Logs::error(__METHOD__, __LINE__, 'Could not move photo to uploads');

					return Response::error('Could not move photo to uploads!');
				}
			}
		} else {
			// Photo already exists
			if ($delete_imported && !is_uploaded_file($tmp_name)) {
				@unlink($tmp_name);
			}
			// Check if the user wants to skip duplicates
			if (Configs::get_value('skip_duplicates', '0') === '1') {
				Logs::notice(__METHOD__, __LINE__, 'Skipped upload of existing photo because skipDuplicates is activated');

				return Response::warning('This photo has been skipped because it\'s already in your library.');
			}
		}

		if ($kind == 'raw') {
			$info = $this->metadataExtractor->bare();
			$this->metadataExtractor->size($info, $path);
			$this->metadataExtractor->validate($info);
			$info['type'] = 'raw';
		} else {
			$info = $this->metadataExtractor->extract($path, $mimeType);
		}

		// Use title of file if IPTC title missing
		if ($kind == 'raw') {
			$info['title'] = substr(basename($file['name']), 0, 98);
		} elseif ($info['title'] === '') {
			$info['title'] = substr(basename($file['name'], $extension), 0, 98);
		}

		$photo->title = $info['title'];
		$photo->url = $photo_name;
		$photo->description = $info['description'];
		$photo->tags = $info['tags'];
		$photo->width = $info['width'] ? $info['width'] : 0;
		$photo->height = $info['height'] ? $info['height'] : 0;
		$photo->type = ($info['type'] ? $info['type'] : $mimeType);
		$photo->size = $info['size'];
		$photo->iso = $info['iso'];
		$photo->aperture = $info['aperture'];
		$photo->make = $info['make'];
		$photo->model = $info['model'];
		$photo->lens = $info['lens'];
		$photo->shutter = $info['shutter'];
		$photo->focal = $info['focal'];
		$photo->takestamp = $info['takestamp'];
		$photo->latitude = $info['latitude'];
		$photo->longitude = $info['longitude'];
		$photo->altitude = $info['altitude'];
		$photo->imgDirection = $info['imgDirection'];
		$photo->livePhotoContentID = $info['livePhotoContentID'];
		$photo->public = $public;
		$photo->star = $star;

		$GoogleMicroVideoOffset = $info['MicroVideoOffset'];

		if ($albumID !== null) {
			$album = Album::find($albumID);
			if ($album == null) {
				$photo->album_id = null;
				$photo->owner_id = $this->sessionFunctions->id();
			} else {
				$photo->album_id = $albumID;
				$photo->owner_id = $album->owner_id;
			}
		} else {
			$photo->album_id = null;
			$photo->owner_id = $this->sessionFunctions->id();
		}

		$livePhotoPartner = false;
		if ($photo->livePhotoContentID) {
			$livePhotoPartner = $photo->findLivePhotoPartner($photo->livePhotoContentID, $photo->album_id);
		}

		$no_error = true;
		$skip_db_entry_creation = false;
		if (!($livePhotoPartner === false)) {
			// if both are a photo or a video -> it's not a live photo
			if (in_array($photo->type, $this->validVideoTypes, true) === in_array($livePhotoPartner->type, $this->validVideoTypes, true)) {
				$livePhotoPartner = false;
			}
		}

		if (!($livePhotoPartner === false)) {
			// I'm uploading a photo, video already exists
			if (!(in_array($photo->type, $this->validVideoTypes, true))) {
				$photo->livePhotoUrl = $livePhotoPartner->url;
				$photo->livePhotoChecksum = $livePhotoPartner->checksum;
				// Todo: Delete the livePhotoPartner
				$no_error &= $livePhotoPartner->predelete(true);
				$no_error &= $livePhotoPartner->delete();
			}
		}

		if ($exists === false) {
			// Generate small files for 2 options:
			// (1) There is no Live Photo Partner
			// (2) There is a partner and we're uploading a photo
			if (($livePhotoPartner === false) || !(in_array($photo->type, $this->validVideoTypes, true))) {
				// Set orientation based on EXIF data
				if ($photo->type === 'image/jpeg' && isset($info['orientation']) && $info['orientation'] !== '') {
					$rotation = $this->imageHandler->autoRotate($path, $info);

					if ($rotation !== [false, false]) {
						$photo->width = $rotation['width'];
						$photo->height = $rotation['height'];
					}
				}

				// Set original date
				if ($info['takestamp'] !== '' && $info['takestamp'] !== 0 && $info['takestamp'] !== null) {
					@touch($path, strtotime($info['takestamp']));
				}

				// For videos extract a frame from the middle
				$frame_tmp = '';
				if (in_array($photo->type, $this->validVideoTypes, true)) {
					try {
						$frame_tmp = $this->extractVideoFrame($photo);
					} catch (Exception $exception) {
						Logs::error(__METHOD__, __LINE__, $exception->getMessage());
					}
				}

				// Create Thumb
				if ($kind == 'raw') {
					$photo->thumbUrl = '';
					$photo->thumb2x = 0;
				} elseif (!in_array($photo->type, $this->validVideoTypes, true) || $frame_tmp !== '') {
					if (!$this->createThumb($photo, $frame_tmp)) {
						Logs::error(__METHOD__, __LINE__, 'Could not create thumbnail for photo');

						return Response::error('Could not create thumbnail for photo!');
					}

					$photo->thumbUrl = basename($photo_name, $extension) . '.jpeg';

					$this->createSmallerImages($photo, $frame_tmp);

					if ($GoogleMicroVideoOffset) {
						$this->extractVideo($photo, $GoogleMicroVideoOffset, $frame_tmp);
					}

					if ($frame_tmp !== '') {
						unlink($frame_tmp);
					}
				} else {
					$photo->thumbUrl = '';
					$photo->thumb2x = 0;
				}
			} else {
				// We're uploading a video -> overwrite everything from partner
				$livePhotoPartner->livePhotoUrl = $photo->url;
				$livePhotoPartner->livePhotoChecksum = $photo->checksum;
				$no_error &= $livePhotoPartner->save();
				$skip_db_entry_creation = true;
			}
		}
		// In case it's a live photo and we've uploaded the video
		if ($skip_db_entry_creation === true) {
			return $livePhotoPartner->id;
		}

		return $this->save($photo, $albumID);
	}

	/**
	 * @param Photo $photo
	 * @param string Path of the video frame
	 *
	 * @return void
	 */
	public function createSmallerImages(Photo $photo, string $frame_tmp = '')
	{
		if ($frame_tmp === '') {
			$mediumMaxWidth = intval(Configs::get_value('medium_max_width'));
			$mediumMaxHeight = intval(Configs::get_value('medium_max_height'));
			$this->resizePhoto($photo, 'medium', $mediumMaxWidth, $mediumMaxHeight);

			if (Configs::get_value('medium_2x') === '1') {
				$this->resizePhoto($photo, 'medium2x', $mediumMaxWidth * 2, $mediumMaxHeight * 2);
			}
		}

		$smallMaxWidth = intval(Configs::get_value('small_max_width'));
		$smallMaxHeight = intval(Configs::get_value('small_max_height'));
		$this->resizePhoto($photo, 'small', $smallMaxWidth, $smallMaxHeight, $frame_tmp);

		if (Configs::get_value('small_2x') === '1') {
			$this->resizePhoto($photo, 'small2x', $smallMaxWidth * 2, $smallMaxHeight * 2, $frame_tmp);
		}
	}

	/**
	 * Creates smaller copies of Photo.
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
			$url = Storage::path('big/' . $filename);
		} else {
			$filename = $photo->thumbUrl;
			$url = $frame_tmp;
		}

		$filename_video_mov = basename($filename, Helpers::getExtension($filename, false)) . '.mov';

		$uploadFolder = Storage::path('big/');

		if (Helpers::hasPermissions($uploadFolder) === false) {
			Logs::notice(__METHOD__, __LINE__, 'Skipped extaction of video from live photo, because ' . $uploadFolder . ' is missing or not readable and writable.');

			return false;
		}

		try {
			// 1. Extract the video part
			$fp = fopen($uploadFolder . $photo->url, 'r+');
			$fp_video = tmpfile(); // use a temporary file, will be delted once closed

			// The MP4 file is located in the last bytes of the file
			fseek($fp, -1 * $videoLengthBytes, SEEK_END); // It needs to be negative
			$data = fread($fp, $videoLengthBytes);
			fwrite($fp_video, $data, $videoLengthBytes);

			// 2. Convert file from mp4 to mov, but keeping audio and video codec
			// This is needed to LivePhotosKit which only accepts mov files
			// Computation is fast, since codecs, resolution, framerate etc. remain unchanged

			$ffmpeg = FFMpeg\FFMpeg::create();
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

	/**
	 * Creates smaller copies of Photo.
	 *
	 * @param Photo  $photo
	 * @param string $type
	 * @param int    $maxWidth
	 * @param int    $maxHeight
	 * @param string Path of the video frame
	 *
	 * @return bool
	 */
	public function resizePhoto(Photo $photo, string $type, int $maxWidth, int $maxHeight, string $frame_tmp = ''): bool
	{
		$width = $photo->width;
		$height = $photo->height;

		if ($frame_tmp === '') {
			$filename = $photo->url;
			$url = Storage::path('big/' . $filename);
		} else {
			$filename = $photo->thumbUrl;
			$url = $frame_tmp;
		}

		// Both image sizes of the same type are stored in the same folder
		// ie: medium and medium2x both belong in LYCHEE_UPLOADS_MEDIUM
		$pathType = strtoupper($type);
		if (($split = strpos($pathType, '2')) !== false) {
			$pathType = substr($pathType, 0, $split);
		}

		$uploadFolder = Storage::path(strtolower($pathType) . '/');
		if (Helpers::hasPermissions($uploadFolder) === false) {
			Logs::notice(__METHOD__, __LINE__, 'Skipped creation of medium-photo, because ' . $uploadFolder . ' is missing or not readable and writable.');

			return false;
		}

		// Add the @2x postfix if we're dealing with an HiDPI type
		if (strpos($type, '2x') > 0) {
			$filename = preg_replace('/^(.*)\.(.*)$/', '\1@2x.\2', $filename);
		}

		// Is photo big enough?
		if (($width <= $maxWidth || $maxWidth == 0) && ($height <= $maxHeight || $maxHeight == 0)) {
			Logs::notice(__METHOD__, __LINE__, 'No resize (image is too small: ' . $maxWidth . 'x' . $maxHeight . ')!');

			return false;
		}

		$resWidth = $resHeight = 0;
		if (!$this->imageHandler->scale($url, $uploadFolder . $filename, $maxWidth, $maxHeight, $resWidth, $resHeight)) {
			Logs::error(__METHOD__, __LINE__, 'Failed to ' . $type . ' resize image');

			return false;
		}

		$photo->{$type} = $resWidth . 'x' . $resHeight;

		return true;
	}

	/**
	 * This function aims to fix the duplicate entry key problem.
	 *
	 * TODO: find where the array to string conversion is...
	 *
	 * @param Photo $photo
	 * @param $albumID
	 *
	 * @return false|mixed|string
	 */
	public function save(Photo $photo, $albumID)
	{
		do {
			$retry = false;

			try {
				if (!$photo->save()) {
					return Response::error('Could not save photo in database!');
				}
			} catch (QueryException $e) {
				$errorCode = $e->getCode();
				if ($errorCode == 23000 || $errorCode == 23505) {
					// houston, we have a duplicate entry problem
					do {
						// Our ids are based on current system time, so
						// wait randomly up to 1s before retrying.
						usleep(rand(0, 1000000));
						$newId = Helpers::generateID();
					} while ($newId === $photo->id);

					$photo->id = $newId;
					$retry = true;
				} else {
					Logs::error(__METHOD__, __LINE__, 'Something went wrong, error ' . $errorCode . ', ' . $e->getMessage());

					return Response::error('Something went wrong, error' . $errorCode . ', please check the logs');
				}
			}
		} while ($retry);

		// Just update the album while we are at it.
		if ($albumID != null) {
			$album = Album::find($albumID);
			if ($album === null) {
				Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

				return Response::error('Could not find specified album');
			}
			if (!$album->update_takestamps([$photo->takestamp], true)) {
				Logs::error(__METHOD__, __LINE__, 'Could not update album takestamps');

				return Response::error('Could not update album takestamps');
			}
		}

		// return the ID.
		return $photo->id;
	}

	/**
	 * Validates whether $type is a valid image type.
	 *
	 * @param int $type
	 *
	 * @return bool
	 */
	public function isValidImageType(int $type): bool
	{
		return in_array($type, $this->validTypes, true);
	}

	/**
	 * Returns a list of valid image types.
	 *
	 * @return array
	 */
	public function getValidImageTypes(): array
	{
		return $this->validTypes;
	}

	/**
	 * Validates whether $type is a valid video type.
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function isValidVideoType(string $type): bool
	{
		return in_array($type, $this->validVideoTypes, true);
	}

	/**
	 * Returns a list of valid video types.
	 *
	 * @return array
	 */
	public function getValidVideoTypes(): array
	{
		return $this->validVideoTypes;
	}

	/**
	 * Validates whether $extension is a valid image or video extension.
	 *
	 * @param string $extension
	 *
	 * @return bool
	 */
	public function isValidExtension(string $extension): bool
	{
		return in_array(strtolower($extension), $this->validExtensions, true);
	}

	/**
	 * Returns a list of valid image/video extensions.
	 *
	 * @return array
	 */
	public function getValidExtensions(): array
	{
		return $this->validExtensions;
	}
}
