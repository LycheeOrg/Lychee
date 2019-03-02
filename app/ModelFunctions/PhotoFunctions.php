<?php

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
	 * @param string $path
	 * @return string Path of the thumbnail
	 */
	public function createVideoThumb(Photo $photo, string $path) : string
{
		$ffprobe = FFMpeg\FFProbe::create();
		$ffmpeg = FFMpeg\FFMpeg::create();
		$duration = $ffprobe
			->format($path)// extracts file informations
			->get('duration');
		$dimension = new FFMpeg\Coordinate\Dimension(400, 400);
		$video = $ffmpeg->open($path);
		$video->filters()->resize($dimension)->synchronize();
		$frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($duration / 2));

		$tmp = tempnam(sys_get_temp_dir(), 'lychee');
		Logs::notice(__METHOD__, __LINE__, 'Saving frame to '.$tmp);
		$frame->save($tmp);

		$thumbUrl = md5($photo->url).'.jpeg';
		$this->imageHandler->crop(
			$tmp,
			Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB').$thumbUrl,
			200,
			200
		);
		$photo->thumb2x = 0;

		Logs::notice(__METHOD__, __LINE__, 'Video thumb saved to '.Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB').$thumbUrl);

		return $thumbUrl;
	}



	/**
	 * @param Photo $photo
	 * @return boolean Returns true when successful.
	 */
	public function createThumb(Photo $photo)
	{
		Logs::notice(__METHOD__, __LINE__, 'Photo URL is '.$photo->url);

		$photoName = explode('.', $photo->url);
		$this->imageHandler->crop(
			Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$photo->url,
			Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB').$photoName[0].'.jpeg',
			200,
			200
		);

		if (Configs::get_value('thumb_2x') === '1' &&
		$photo->width >= 400 && $photo->height >= 400) {
			// Retina thumbs
			$this->imageHandler->crop(
				Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$photo->url,
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
	 * Creates a smaller version of a photo when its size is bigger than a preset size.
	 * Photo must be big enough.
	 * @param Photo $photo
	 * @param int $newWidth
	 * @param int $newHeight
	 * @param $resWidth
	 * @param $resHeight
	 * @param bool $x2
	 * @param string $kind
	 * @return boolean Returns true when successful.
	 */
	public function createMedium(Photo $photo, $newWidth, $newHeight, &$resWidth, &$resHeight, $x2 = false, $kind = 'MEDIUM')
	{
		$filename = $photo->url;
		$width = $photo->width;
		$height = $photo->height;

		$url = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$filename;

		// Check permissions
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_'.$kind)) === false) {

			// Permissions are missing
			Logs::notice(__METHOD__, __LINE__, 'Skipped creation of medium-photo, because '.Config::get('defines.dirs.LYCHEE_UPLOADS_'.$kind).' is missing or not readable and writable.');
			return false;

		}

		if ($x2) {
			$newWidth *= 2;
			$newHeight *= 2;

			$photoName = explode('.', $filename);
			$filename = $photoName[0].'@2x.'.$photoName[1];
		}

		// Is photo big enough?
		if (($width <= $newWidth || $newWidth == 0) && ($height <= $newHeight || $newHeight == 0)) {
			Logs::notice(__METHOD__, __LINE__, 'No resize (image is too small)!');
			return false;
		}

		$newUrl = Config::get('defines.dirs.LYCHEE_UPLOADS_'.$kind).$filename;

		return $this->imageHandler->scale($url, $newUrl, $newWidth, $newHeight, $resWidth, $resHeight);

	}



	/**
	 * Creats new photo(s).
	 * Exits on error.
	 *
	 * @param array $file
	 * @param int $albumID_in
	 * @return string|false ID of the added photo.
	 */
	public function add(array $file, $albumID_in = 0)
	{
		$id = Session::get('UserID');

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

			// r for recent
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

		$info = $this->metadataExtractor->extract($path);

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
		$photo->owner_id = Session::get('UserID');
		$photo->star = $star;
		$photo->checksum = $checksum;
		$photo->album_id = $albumID;

		if ($exists === false) {

			// Set orientation based on EXIF data
			if ($photo->type === 'image/jpeg' && isset($info['orientation']) && $info['orientation'] !== '') {
				$rotation = $this->imageHandler->autoRotate($path, $info);

				$photo->width = $rotation['width'];
				$photo->height = $rotation['height'];
			}

			// Set original date
			if ($info['takestamp'] !== '' && $info['takestamp'] !== 0) {
				@touch($path, $info['takestamp']);
			}

			// Create Thumb
			if (!in_array($photo->type, $this->validVideoTypes, true)) {

				if (!$this->createThumb($photo)) {
					Logs::error(__METHOD__, __LINE__, 'Could not create thumbnail for photo');
					return Response::error('Could not create thumbnail for photo!');
				}

				$path_thumb = basename($photo_name, $extension).".jpeg";
			}
			else {
				try {
					$path_thumb = $this->createVideoThumb($photo, $path);
				} catch (\Exception $exception) {
					Logs::error(__METHOD__, __LINE__, $exception->getMessage());
					$path_thumb = '';
				}
			}

			Logs::notice(__METHOD__, __LINE__, $path_thumb);
			$photo->thumbUrl = $path_thumb;

			$resWidth = 0;
			$resHeight = 0;

			// Create Medium
			if ($this->createMedium($photo, intval(Configs::get_value('medium_max_width')), intval(Configs::get_value('medium_max_height')), $resWidth, $resHeight)) {
				$photo->medium = $resWidth . 'x' . $resHeight;

				if (Configs::get_value('medium_2x') === '1' &&
				$this->createMedium($photo, intval(Configs::get_value('medium_max_width')), intval(Configs::get_value('medium_max_height')), $resWidth, $resHeight, true)) {
					$photo->medium2x = $resWidth . 'x' . $resHeight;
				}
				else {
					$photo->medium2x = '';
				}
			}
			else {
				$photo->medium = '';
				$photo->medium2x = '';
			}

			// Create Small
			if ($this->createMedium($photo, intval(Configs::get_value('small_max_width')), intval(Configs::get_value('small_max_height')), $resWidth, $resHeight, false, 'SMALL')) {
				$photo->small = $resWidth . 'x' . $resHeight;

				if (Configs::get_value('small_2x') === '1' &&
				$this->createMedium($photo, intval(Configs::get_value('small_max_width')), intval(Configs::get_value('small_max_height')), $resWidth, $resHeight, true, 'SMALL')) {
					$photo->small2x = $resWidth . 'x' . $resHeight;
				}
				else {
					$photo->small2x = '';
				}
			}
			else {
				$photo->small = '';
				$photo->small2x = '';
			}
		}

		return $this->save($photo, $albumID);
	}



	/**
	 * We create this recursive function to try to fix the duplicate entry key problem
	 *
	 * @param Photo $photo
	 * @param $albumID
	 * @return false|mixed|string
	 */
	public function save(Photo $photo, $albumID)
	{
		// quick check to see if there is a duplicate and regenerate the ID if needed..
		while (Photo::where('id', '=', $photo->id)->exists()) {
			$photo->id = Helpers::generateID();
		};

		try {
			if (!$photo->save()) {
				return Response::error('Could not save photo in database!');
			}
		}
		catch (QueryException $e) {
			// We have a QueryException, something went VERY WRONG.

			$errorCode = $e->errorInfo[1];
			if ($errorCode == 1062) {
				// houston, we have a duplicate entry problem
				// we change the ID and recurse the function


				return $this->save($photo, $albumID);
			}
			else if ($errorCode == 1264) {
				Logs::error(__METHOD__, __LINE__, 'Id is a bit too big... '.$photo->id);
				return Response::error('Id is a bit too big... '.$photo->id);
			}
			else {
				Logs::error(__METHOD__, __LINE__, 'Something went wrong, error '.$errorCode);
				return Response::error('Something went wrong, error'.$errorCode);
			}
		}

		// Just update the album while we are at it.
		if ($albumID != null) {
			$album = Album::find($albumID);
			if ($album === null) {
				Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
				return Response::error('Could not find specified album');
			}
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
	 * @param  int $type
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
	 * @param  string $type
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
	 * @param  string $extension
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
