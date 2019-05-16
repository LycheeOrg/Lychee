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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

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
	 * @var array
	 */
	public $validTypes = array(
		IMAGETYPE_JPEG,
		IMAGETYPE_GIF,
		IMAGETYPE_PNG
	);

	/**
	 * @var array
	 */
	public $validVideoTypes = array(
		"video/mp4",
		"video/ogg",
		"video/webm",
		"video/quicktime"
	);

	/**
	 * @var array
	 */
	public $validExtensions = array(
		'.jpg',
		'.jpeg',
		'.png',
		'.gif',
		'.ogv',
		'.mp4',
		'.webm',
		'.mov'
	);



	public function __construct(Extractor $metadataExtractor, ImageHandlerInterface $imageHandler)
	{
		$this->metadataExtractor = $metadataExtractor;
		$this->imageHandler = $imageHandler;
	}



	/**
	 * @param Photo $photo
	 * @return string Path of the video frame
	 */
	public function extractVideoFrame(Photo $photo): string
	{
		if ($photo->aperture === '') {
			return '';
		}

		$ffmpeg = FFMpeg\FFMpeg::create();
		$video = $ffmpeg->open(Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$photo->url);
		$frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($photo->aperture / 2));

		$tmp = tempnam(sys_get_temp_dir(), 'lychee');
		Logs::notice(__METHOD__, __LINE__, 'Saving frame to '.$tmp);
		$frame->save($tmp);

		return $tmp;
	}



	/**
	 * @param Photo $photo
	 * @param string Path of the video frame
	 * @return boolean Returns true when successful.
	 */
	public function createThumb(Photo $photo, string $frame_tmp = '')
	{
		Logs::notice(__METHOD__, __LINE__, 'Photo URL is '.$photo->url);

		$src = ($frame_tmp === '') ? Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$photo->url : $frame_tmp;
		$photoName = explode('.', $photo->url);
		$this->imageHandler->crop(
			$src,
			Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB').$photoName[0].'.jpeg',
			200,
			200
		);

		if (Configs::get_value('thumb_2x') === '1' &&
			$photo->width >= 400 && $photo->height >= 400) {
			// Retina thumbs
			$this->imageHandler->crop(
				$src,
				Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB').$photoName[0].'@2x.jpeg',
				400,
				400
			);
			$photo->thumb2x = 1;
		}
		else {
			$photo->thumb2x = 0;
		}

		return true;
	}



	/**
	 * Add new photo(s) to the database.
	 * Exits on error.
	 *
	 * @param array $file
	 * @param int $albumID_in
	 * @return string|false ID of the added photo.
	 */
	public function add(array $file, $albumID_in = 0)
	{
		// Check permissions
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS')) === false ||
			Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_BIG')) === false ||
			Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM')) === false ||
			Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB')) === false) {
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
			case 'r':
				$public = 0;
				$star = 0;
				$albumID = null;
				break;

			// 0 for unsorted
			case '0':
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
		if (!in_array(strtolower($extension), $this->validExtensions, true)) {
			Logs::error(__METHOD__, __LINE__, 'Photo format not supported');
			return Response::error('Photo format not supported!');
		}

		// should not be needed
		// Verify video
		$mimeType = $file['type'];
		if (!in_array($mimeType, $this->validVideoTypes, true)) {

			if (!function_exists("exif_imagetype")) {
				Logs::error(__METHOD__, __LINE__, 'EXIF library not loaded. Make sure exif is enabled in php.ini');
				return Response::error('EXIF library not loaded on the server!');
			}

			// Verify image
			$type = @exif_imagetype($file['tmp_name']);
			if (!in_array($type, $this->validTypes, true)) {
				Logs::error(__METHOD__, __LINE__, 'Photo type not supported');
				return Response::error('Photo type not supported!');
			}
		}

		// Generate id
		$photo = new Photo();
		$photo->id = Helpers::generateID();

		// Set paths
		$tmp_name = $file['tmp_name'];
		$photo_name = md5(microtime()).$extension;
		$path = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$photo_name;

		// Calculate checksum
		$checksum = sha1_file($tmp_name);
		if ($checksum === false) {
			Logs::error(__METHOD__, __LINE__, 'Could not calculate checksum for photo');
			return Response::error('Could not calculate checksum for photo!');
		}


		$exists = $photo->isDuplicate($checksum);

		// double check that
		if ($exists !== false) {
			$photo_name = $exists->url;
			$path = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$exists->url;
			$photo->thumbUrl = $exists->thumbUrl;
			$photo->thumb2x = $exists->thumb2x;
			$photo->medium = $exists->medium;
			$photo->medium2x = $exists->medium2x;
			$photo->small = $exists->small;
			$photo->small2x = $exists->small2x;
			$exists = true;
		}


		if ($exists === false) {

			// Import if not uploaded via web
			if (!is_uploaded_file($tmp_name)) {
				if (!@copy($tmp_name, $path)) {
					Logs::error(__METHOD__, __LINE__, 'Could not copy photo to uploads');
					return Response::error('Could not copy photo to uploads!');
				}
				elseif (Configs::get_value('deleteImported') === '1') {
					@unlink($tmp_name);
				}
			}
			else {
				if (!@move_uploaded_file($tmp_name, $path)) {
					Logs::error(__METHOD__, __LINE__, 'Could not move photo to uploads');
					return Response::error('Could not move photo to uploads!');
				}
			}

		}
		else {

			// Photo already exists
			// Check if the user wants to skip duplicates
			if (Configs::get()['skipDuplicates'] === '1') {
				Logs::notice(__METHOD__, __LINE__, 'Skipped upload of existing photo because skipDuplicates is activated');
				return Response::warning('This photo has been skipped because it\'s already in your library.');
			}

		}

		$info = $this->metadataExtractor->extract($path, $mimeType);

		// Use title of file if IPTC title missing
		if ($info['title'] === '') {
			$info['title'] = substr(basename($file['name'], $extension), 0, 30);
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
		$photo->public = $public;
		$photo->star = $star;
		$photo->checksum = $checksum;

		if ($albumID !== null) {
			$album = Album::find($albumID);
			if ($album == null) {
				$photo->album_id = null;
				$photo->owner_id = Session::get('UserID');
			}
			else {
				$photo->album_id = $albumID;
				$photo->owner_id = $album->owner_id;
			}
		}
		else {
			$photo->album_id = null;
			$photo->owner_id = Session::get('UserID');
		}

		if ($exists === false) {

			// Set orientation based on EXIF data
			if ($photo->type === 'image/jpeg' && isset($info['orientation']) && $info['orientation'] !== '') {
				$rotation = $this->imageHandler->autoRotate($path, $info);

				$photo->width = $rotation['width'];
				$photo->height = $rotation['height'];
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
				}
				catch (Exception $exception) {
					Logs::error(__METHOD__, __LINE__, $exception->getMessage());
				}
			}

			// Create Thumb
			if (!in_array($photo->type, $this->validVideoTypes, true) || $frame_tmp !== '') {
				if (!$this->createThumb($photo, $frame_tmp)) {
					Logs::error(__METHOD__, __LINE__, 'Could not create thumbnail for photo');
					return Response::error('Could not create thumbnail for photo!');
				}

				$photo->thumbUrl = basename($photo_name, $extension).".jpeg";

				$this->createSmallerImages($photo, $frame_tmp);

				if ($frame_tmp !== '') {
					unlink($frame_tmp);
				}
			}
			else {
				$photo->thumbUrl = '';
				$photo->thumb2x = 0;
			}
		}

		return $this->save($photo, $albumID);
	}



	/**
	 * @param Photo $photo
	 * @param string Path of the video frame
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
	 * Creates smaller copies of Photo
	 *
	 * @param Photo $photo
	 * @param string $type
	 * @param int $maxWidth
	 * @param int $maxHeight
	 * @param string Path of the video frame
	 * @return bool
	 */
	public function resizePhoto(Photo $photo, string $type, int $maxWidth, int $maxHeight, string $frame_tmp = ''): bool
	{
		$width = $photo->width;
		$height = $photo->height;

		if ($frame_tmp === '') {
			$filename = $photo->url;
			$url = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$filename;
		}
		else {
			$filename = $photo->thumbUrl;
			$url = $frame_tmp;
		}

		// Both image sizes of the same type are stored in the same folder
		// ie: medium and medium2x both belong in LYCHEE_UPLOADS_MEDIUM
		$pathType = strtoupper($type);
		if (($split = strpos($pathType, '2')) !== false) {
			$pathType = substr($pathType, 0, $split);
		}

		$uploadFolder = Config::get('defines.dirs.LYCHEE_UPLOADS_'.$pathType);
		if (Helpers::hasPermissions($uploadFolder) === false) {
			Logs::notice(__METHOD__, __LINE__, 'Skipped creation of medium-photo, because '.$uploadFolder.' is missing or not readable and writable.');
			return false;
		}

		// Add the @2x postfix if we're dealing with an HiDPI type
		if (strpos($type, '2x') > 0) {
			$filename = preg_replace('/^(.*)\.(.*)$/', '\1@2x.\2', $filename);
		}

		// Is photo big enough?
		if (($width <= $maxWidth || $maxWidth == 0) && ($height <= $maxHeight || $maxHeight == 0)) {
			Logs::notice(__METHOD__, __LINE__, 'No resize (image is too small: '.$maxWidth.'x'.$maxHeight.')!');
			return false;
		}

		$resWidth = $resHeight = 0;
		if (!$this->imageHandler->scale($url, $uploadFolder.$filename, $maxWidth, $maxHeight, $resWidth, $resHeight)) {
			Logs::error(__METHOD__, __LINE__, 'Failed to '.$type.' resize image');
			return false;
		}

		$photo->{$type} = $resWidth.'x'.$resHeight;

		return true;
	}



	/**
	 * We create this function to try to fix the duplicate entry key problem
	 *
	 * @param Photo $photo
	 * @param $albumID
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
			}
			catch (QueryException $e) {
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
				}
				else {
					Logs::error(__METHOD__, __LINE__, 'Something went wrong, error '.$errorCode.', '.$e->getMessage());
					return Response::error('Something went wrong, error'.$errorCode.', please check the logs');
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
			// TODO: should also recursively update the parent albums.
			$album->update_min_max_takestamp();
			if (!$album->save()) {
				return Response::error('Could not update album takestamp in database!');
			}
		}

		// return the ID.
		return $photo->id;

	}



	/**
	 * Validates whether $type is a valid image type.
	 *
	 * @param int $type
	 * @return boolean
	 */
	public function isValidImageType(int $type): bool
	{
		return in_array($type, $this->validTypes, true);
	}



	/**
	 * Returns a list of valid image types
	 *
	 * @return array
	 */
	public function getValidImageTypes(): array
	{
		return $this->validTypes;
	}



	/**
	 * Validates whether $type is a valid video type
	 *
	 * @param string $type
	 * @return boolean
	 */
	public function isValidVideoType(string $type): bool
	{
		return in_array($type, $this->validVideoTypes, true);
	}



	/**
	 * Returns a list of valid video types
	 *
	 * @return array
	 */
	public function getValidVideoTypes(): array
	{
		return $this->validVideoTypes;
	}



	/**
	 * Validates whether $extension is a valid image or video extension
	 *
	 * @param string $extension
	 * @return boolean
	 */
	public function isValidExtension(string $extension): bool
	{
		return in_array(strtolower($extension), $this->validExtensions, true);
	}



	/**
	 * Returns a list of valid image/video extensions
	 *
	 * @return array
	 */
	public function getValidExtensions(): array
	{
		return $this->validExtensions;
	}
}
