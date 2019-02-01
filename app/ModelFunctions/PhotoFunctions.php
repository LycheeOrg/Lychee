<?php

namespace App\ModelFunctions;

use App\Album;
use App\Configs;
use App\Logs;
use App\Photo;
use App\Response;
use App\Metadata\Extractor;
use Exception;
use FFMpeg;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Imagick;
use ImagickException;
use ImagickPixel;

class PhotoFunctions
{
	/**
	 * @var Extractor
	 */
	private $metadataExtractor;

	public function __construct(Extractor $metadataExtractor)
	{
		$this->metadataExtractor = $metadataExtractor;
	}

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

	/**
	 * @param string $checksum
	 * @param $photoID
	 * @return array|false Returns a subset of a photo when same photo exists or returns false on failure.
	 */
	public function exists(string $checksum, $photoID = null)
	{

		$sql = Photo::where('checksum', '=', $checksum);
		if (isset($photoID)) {
			$sql = $sql->where('id', '<>', $photoID);
		}

		return ($sql->count() == 0) ? false : $sql->first();
	}

	/**
	 * Rotates and flips a photo based on its EXIF orientation.
	 * @param $path
	 * @param array $info
	 * @return array|false Returns an array with the new orientation, width, height or false on failure.
	 * @throws ImagickException
	 */
	public function adjustFile($path, array $info)
	{

		// Excepts the following:
		// (string) $path = Path to the photo-file
		// (array) $info = ['orientation', 'width', 'height']

		$swapSize = false;

		if (extension_loaded('imagick') && Configs::get()['imagick'] === '1') {

			$image = new Imagick();
			$image->readImage($path);

			$orientation = $image->getImageOrientation();

			switch ($orientation) {

				case Imagick::ORIENTATION_TOPLEFT:
					return false;
					break;
				case Imagick::ORIENTATION_TOPRIGHT:
					$image->flopImage();
					break;
				case Imagick::ORIENTATION_BOTTOMRIGHT:
					$image->rotateImage(new ImagickPixel(), 180);
					break;
				case Imagick::ORIENTATION_BOTTOMLEFT:
					$image->flopImage();
					$image->rotateImage(new ImagickPixel(), 180);
					break;
				case Imagick::ORIENTATION_LEFTTOP:
					$image->flopImage();
					$image->rotateImage(new ImagickPixel(), -90);
					$swapSize = true;
					break;
				case Imagick::ORIENTATION_RIGHTTOP:
					$image->rotateImage(new ImagickPixel(), 90);
					$swapSize = true;
					break;
				case Imagick::ORIENTATION_RIGHTBOTTOM:
					$image->flopImage();
					$image->rotateImage(new ImagickPixel(), 90);
					$swapSize = true;
					break;
				case Imagick::ORIENTATION_LEFTBOTTOM:
					$image->rotateImage(new ImagickPixel(), -90);
					$swapSize = true;
					break;
				default:
					return false;
					break;

			}

			// Adjust photo
			$image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
			$image->writeImage($path);

			// Free memory
			$image->clear();
			$image->destroy();

		}
		else {

			$newWidth = $info['width'];
			$newHeight = $info['height'];
			$sourceImg = imagecreatefromjpeg($path);

			switch ($info['orientation']) {

				// do nothing
				case 1:
					return false;
					break;

				// mirror
				case 2:
					imageflip($sourceImg, IMG_FLIP_HORIZONTAL);
					break;

				case 3:
					$sourceImg = imagerotate($sourceImg, -180, 0);
					break;

				// rotate 180 and mirror
				case 4:
					imageflip($sourceImg, IMG_FLIP_VERTICAL);
					break;

				// rotate 90 and mirror
				case 5:
					$sourceImg = imagerotate($sourceImg, -90, 0);
					$newWidth = $info['height'];
					$newHeight = $info['width'];
					$swapSize = true;
					imageflip($sourceImg, IMG_FLIP_HORIZONTAL);
					break;

				case 6:
					$sourceImg = imagerotate($sourceImg, -90, 0);
					$newWidth = $info['height'];
					$newHeight = $info['width'];
					$swapSize = true;
					break;

				// rotate -90 and mirror
				case 7:
					$sourceImg = imagerotate($sourceImg, 90, 0);
					$newWidth = $info['height'];
					$newHeight = $info['width'];
					$swapSize = true;
					imageflip($sourceImg, IMG_FLIP_HORIZONTAL);
					break;

				case 8:
					$sourceImg = imagerotate($sourceImg, 90, 0);
					$newWidth = $info['height'];
					$newHeight = $info['width'];
					$swapSize = true;
					break;

				default:
					return false;
					break;

			}

			// Recreate photo
			// In this step the photos also loses its metadata :(
			$newSourceImg = imagecreatetruecolor($newWidth, $newHeight);
			imagecopyresampled($newSourceImg, $sourceImg, 0, 0, 0, 0, $newWidth, $newHeight, $newWidth, $newHeight);
			imagejpeg($newSourceImg, $path, 100);

			// Free memory
			imagedestroy($sourceImg);
			imagedestroy($newSourceImg);

		}

		// SwapSize should be true when the image has been rotated
		// Return new dimensions in this case
		if ($swapSize === true) {
			$swapSize = $info['width'];
			$info['width'] = $info['height'];
			$info['height'] = $swapSize;
		}

		return $info;

	}



	/**
	 * @param Photo $photo
	 * @param string $path
	 * @param $id
	 * @return boolean Returns true when successful.
	 */
	public function createVideoThumb(Photo $photo, string $path, $id)
	{
		try {
			$ffprobe = FFMpeg\FFProbe::create();
			$ffmpeg = FFMpeg\FFMpeg::create();
			$duration = $ffprobe
				->format($path)// extracts file informations
				->get('duration');
			$dimension = new FFMpeg\Coordinate\Dimension(200, 200);
			$video = $ffmpeg->open($path);
			$video->filters()->resize($dimension)->synchronize();
			$frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($duration / 2));
			$frame->save(sys_get_temp_dir().'/'.md5($id).'.jpeg');
			$info = $photo->getInfo(sys_get_temp_dir().'/'.md5($id).'.jpeg');
			if (!$photo->createThumb(sys_get_temp_dir().'/'.md5($id).'.jpeg', md5($id).'.jpeg', $info['type'], $info['width'], $info['height'])) {
				Logs::error(__METHOD__, __LINE__, 'Could not create thumbnail for video');
			}
			return true;
		}
		catch (Exception $exception) {
			return false;
		}
	}



	/**
	 * @param Photo $photo
	 * @return boolean Returns true when successful.
	 */
	public function createThumb(Photo $photo)
	{

		$filename = $photo->url;
		$type = $photo->type;
		$width = $photo->width;
		$height = $photo->height;
		$url = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$filename;
		// Quality of thumbnails
		$quality = 90;

		// Size of the thumbnail
		$newWidth = 200;
		$newHeight = 200;

		$photoName = explode('.', $filename);
		$newUrl = Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB').$photoName[0].'.jpeg';
		$newUrl2x = Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB').$photoName[0].'@2x.jpeg';

		$error = false;
		// Create thumbnails with Imagick
		if (Configs::hasImagick()) {

			try {
				// Read image
				$thumb = new Imagick();
				$thumb->readImage($url);
				$thumb->setImageCompressionQuality($quality);
				$thumb->setImageFormat('jpeg');

				// Remove metadata to save some bytes
				$thumb->stripImage();

				// Copy image for 2nd thumb version
				$thumb2x = clone $thumb;

				// Create 1st version
				$thumb->cropThumbnailImage($newWidth, $newHeight);
				$thumb->writeImage($newUrl);
				$thumb->clear();
				$thumb->destroy();

				// Create 2nd version
				$thumb2x->cropThumbnailImage($newWidth * 2, $newHeight * 2);
				$thumb2x->writeImage($newUrl2x);
				$thumb2x->clear();
				$thumb2x->destroy();
			}
			catch (ImagickException $exception) {
				Logs::error(__METHOD__, __LINE__, $exception->getMessage());
				$error = true;
			}

		}
		else {
			$error = true;
		}

		if ($error) {

			// Create image
			$thumb = imagecreatetruecolor($newWidth, $newHeight);
			$thumb2x = imagecreatetruecolor($newWidth * 2, $newHeight * 2);

			// Set position
			if ($width < $height) {
				$newSize = $width;
				$startWidth = 0;
				$startHeight = $height / 2 - $width / 2;
			}
			else {
				$newSize = $height;
				$startWidth = $width / 2 - $height / 2;
				$startHeight = 0;
			}

			// Create new image
			switch ($type) {
				case 'image/jpeg':
					$sourceImg = imagecreatefromjpeg($url);
					break;
				case 'image/png':
					$sourceImg = imagecreatefrompng($url);
					break;
				case 'image/gif':
					$sourceImg = imagecreatefromgif($url);
					break;
				default:
					Logs::error(__METHOD__, __LINE__, 'Type of photo is not supported');
					return false;
					break;
			}

			// Create thumb
			Helpers::fastImageCopyResampled($thumb, $sourceImg, 0, 0, $startWidth, $startHeight, $newWidth, $newHeight, $newSize, $newSize);
			imagejpeg($thumb, $newUrl, $quality);
			imagedestroy($thumb);

			// Create retina thumb
			Helpers::fastImageCopyResampled($thumb2x, $sourceImg, 0, 0, $startWidth, $startHeight, $newWidth * 2, $newHeight * 2, $newSize, $newSize);
			imagejpeg($thumb2x, $newUrl2x, $quality);
			imagedestroy($thumb2x);

			// Free memory
			imagedestroy($sourceImg);

		}

		return true;

	}



	/**
	 * Creates a smaller version of a photo when its size is bigger than a preset size.
	 * Photo must be big enough and Imagick must be installed and activated.
	 * @param Photo $photo
	 * @param int $newWidth
	 * @param int $newHeight
	 * @param string $kind
	 * @return boolean Returns true when successful.
	 */
	public function createMedium(Photo $photo, $newWidth = 1920, $newHeight = 1080, $kind = 'MEDIUM')
	{

		// Excepts the following:
		// (string) $url = Path to the photo-file
		// (string) $filename = Name of the photo-file
		// (int) $width = Width of the photo
		// (int) $height = Height of the photo

		$filename = $photo->url;
		$width = $photo->width;
		$height = $photo->height;

		$url = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$filename;

		// Quality of medium-photo
		$quality = 90;

		// Size of the medium-photo
		// When changing these values,
		// also change the size detection in the front-end

		// Check permissions
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_'.$kind)) === false) {

			// Permissions are missing
			Logs::notice(__METHOD__, __LINE__, 'Skipped creation of medium-photo, because '.Config::get('defines.dirs.LYCHEE_UPLOADS_'.$kind).' is missing or not readable and writable.');
			return false;

		}

		// Is photo big enough?
		// Is Imagick installed and activated?
		if ($width <= $newWidth && $height <= $newHeight) {
			Logs::notice(__METHOD__, __LINE__, 'No resize (image is too small)!');
			return false;
		}

		$newUrl = Config::get('defines.dirs.LYCHEE_UPLOADS_'.$kind).$filename;

		$error = false;
		if (Configs::hasImagick()) {
			Logs::notice(__METHOD__, __LINE__, 'Picture is big enough for resize!');

			try {
				// Read image
				$medium = new Imagick();
				$medium->readImage($url);

				// Adjust image
				$medium->scaleImage($newWidth, $newHeight, ($newWidth != 0));
				$medium->stripImage();
				$medium->setImageCompressionQuality($quality);

				// Save image
				try {
					$medium->writeImage($newUrl);
				}
				catch (ImagickException $err) {
					Logs::notice(__METHOD__, __LINE__, 'Could not save '.$kind.'-photo ('.$err->getMessage().')');
					$error = true;
				}

				$medium->clear();
				$medium->destroy();
			}
			catch (ImagickException $exception) {
				Logs::error(__METHOD__, __LINE__, $exception->getMessage());
				$error = true;
			}

		}

		if ($error || !Configs::hasImagick()) {
			Logs::notice(__METHOD__, __LINE__, 'Picture is big enough for resize, try with GD!');

			// Create image
			if ($newWidth == 0) {
				$newWidth = $newHeight * ($width / $height);
			}
			else {
				$newHeight = $newWidth / ($width / $height);
			}

			$medium = imagecreatetruecolor($newWidth, $newHeight);
			// Create new image
			switch ($photo->type) {
				case 'image/jpeg':
					$sourceImg = imagecreatefromjpeg($url);
					break;
				case 'image/png':
					$sourceImg = imagecreatefrompng($url);
					break;
				case 'image/gif':
					$sourceImg = imagecreatefromgif($url);
					break;
				default:
					Logs::error(__METHOD__, __LINE__, 'Type of photo is not supported');
					return false;
					break;
			}
			// Create retina thumb
			imagecopyresampled($medium, $sourceImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			imagejpeg($medium, $newUrl, $quality);
			imagedestroy($medium);
			// Free memory
			imagedestroy($sourceImg);

			$error = false;
		}
		return !$error;

	}



	/**
	 * Creats new photo(s).
	 * Exits on error.
	 * Use $returnOnError if you want to handle errors by your own.
	 * @param array $file
	 * @param int $albumID_in
	 * @return string|false ID of the added photo.
	 * @throws ImagickException
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


		$exists = $this->exists($checksum);

		// double check that
		if ($exists !== false) {
			$photo_name = $exists->url;
			$path = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$exists->url;
			$path_thumb = $exists->thumbUrl;
			$medium = $exists->medium;
			$small = $exists->small;
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
		$photo->medium = 0;
		$photo->small = 0;

		if ($exists === false) {

			// Set orientation based on EXIF data
			if ($photo->type === 'image/jpeg' && isset($info['orientation']) && $info['orientation'] !== '') {
				$adjustFile = $this->adjustFile($path, $info);
				if ($adjustFile !== false) {
					$info = $adjustFile;
				}
				else {
					Logs::notice(__METHOD__, __LINE__, 'Skipped adjustment of photo ('.$info['title'].')');
				}
			}

			$photo->width = $info['width'];
			$photo->height = $info['height'];

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
			elseif (!defined('VIDEO_THUMB')) {
				Logs::notice(__METHOD__, __LINE__, 'Could not create thumbnail for video because FFMPEG is not available.');
				// Set thumb url
				$path_thumb = '';
			}
			else {
				if (!$this->createVideoThumb($photo, $path, $id)) {
					Logs::error(__METHOD__, __LINE__, 'Could not create thumbnail for video');
					$path_thumb = '';
				}
				else {
					// Set thumb url
					$path_thumb = md5($id).'.jpeg';
				}
			}

			// Create Medium
			if ($this->createMedium($photo, intval(Configs::get_value('medium_max_width')), intval(Configs::get_value('medium_max_height')))) {
				$medium = 1;
			}
			else {
				$medium = 0;
			}

			// Create Small
			if ($this->createMedium($photo, intval(Configs::get_value('small_max_width')), intval(Configs::get_value('small_max_height')), 'SMALL')) {
				$small = 1;
			}
			else {
				$small = 0;
			}
		}

		$photo->thumbUrl = $path_thumb;
		$photo->medium = $medium;
		$photo->small = $small;
		if (!$photo->save()) {
			return Response::error('Could not save photo in database!');
		}

		if ($albumID != null) {
			$album = Album::find($albumID);
			if ($album === null) {
				Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
				return 'false';
			}
			$album->update_min_max_takestamp();
			if (!$album->save()) {
				return Response::error('Could not update album takestamp in database!');
			}
		}

		return $photo->id;
	}

	/**
	 * Validates whether $type is a valid image type.
	 * 
	 * @param  int     $type
	 * @return boolean
	 */
	public function isValidImageType(int $type) : bool
	{
		return in_array($type, $this->validTypes, true);
	}

	/**
	 * Returns a list of valid image types
	 *
	 * @return array
	 */
	public function getValidImageTypes() : array
	{
		return $this->validTypes;
	}

	/**
	 * Validates whether $type is a valid video type
	 * 
	 * @param  string  $type
	 * @return boolean
	 */
	public function isValidVideoType(string $type) : bool
	{
		return in_array($type, $this->validVideoTypes, true);
	}

	/**
	 * Returns a list of valid video types
	 *
	 * @return array
	 */
	public function getValidVideoTypes() : array
	{
		return $this->validVideoTypes;
	}

	/**
	 * Validates whether $extension is a valid image or video extension
	 * 
	 * @param  string  $extension
	 * @return boolean
	 */
	public function isValidExtension(string $extension) : bool
	{
		return in_array(strtolower($extension), $this->validExtensions, true);
	}

	/**
	 * Returns a list of valid image/video extensions
	 *
	 * @return array
	 */
	public function getValidExtensions() : array
	{
		return $this->validExtensions;
	}
}