<?php
/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\ControllerFunctions\ReadAccessFunctions;
use App\Logs;
use App\ModelFunctions\Helpers;
use App\ModelFunctions\PhotoFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Photo;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class PhotoController extends Controller
{
	/**
	 * @var PhotoFunctions
	 */
	private $photoFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;


	/**
	 * @var ReadAccessFunctions
	 */
	private $readAccessFunctions;



	/**
	 * @param PhotoFunctions $photoFunctions
	 * @param SessionFunctions $sessionFunctions
	 * @param ReadAccessFunctions $readAccessFunctions
	 */
	public function __construct(PhotoFunctions $photoFunctions, SessionFunctions $sessionFunctions, ReadAccessFunctions $readAccessFunctions)
	{
		$this->photoFunctions = $photoFunctions;
		$this->sessionFunctions = $sessionFunctions;
		$this->readAccessFunctions = $readAccessFunctions;
	}



	/**
	 * @param Request $request
	 * @return array|string
	 */
	function get(Request $request)
	{
		$request->validate([
			// we actually don't care about that one...
			'albumID' => 'string|required',

			'photoID' => 'string|required'
		]);

		$photo = Photo::with('album')->find($request['photoID']);

		// Photo not found?
		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
			return 'false';
		}

		if ($this->readAccessFunctions->photo($photo)) {

			$return = $photo->prepareData();
			$return['original_album'] = $return['album'];
			$return['album'] = $photo->album_id;
			return $return;
		}

		Logs::error(__METHOD__, __LINE__, 'Accessing non public photo: '.$photo->id);
		return 'false';
	}



	/**
	 * @return string
	 */
	function getRandom()
	{


		// here we need to refine.

		$photo = Photo::where('photos.star', '=', 1)
			->join('albums', 'album_id', '=', 'albums.id')
			->where('albums.public', '=', '1')
			->inRandomOrder()
			->first();

		if ($photo == null) {
			return Response::error('no pictures found!');
		}

		$return = $photo->prepareData();

		return $return;
	}



	/**
	 * @param Request $request
	 * @return false|string
	 */
	function add(Request $request)
	{
		$request->validate([
			'albumID' => 'string|required',
			'0'       => 'required',
			//            '0.*' => 'image|mimes:jpeg,png,jpg,gif',
			'0.*'     => 'image|mimes:jpeg,png,jpg,gif,mov,webm,mp4,ogv',
			//                |max:2048'
		]);

//		$id = Session::get('UserID');

		if (!$request->hasfile('0')) {
			return Response::error('missing files');
		}

		// Only process the first photo in the array
		$file = $request->file('0');

		$nameFile = array();
		$nameFile['name'] = $file->getClientOriginalName();
		$nameFile['type'] = $file->getMimeType();
		$nameFile['tmp_name'] = $file->getPathName();

		return $this->photoFunctions->add($nameFile, $request['albumID']);
	}



	/**
	 * @param Request $request
	 * @return string
	 */
	function setTitle(Request $request)
	{

		$request->validate([
			'photoIDs' => 'required|string',
			'title'    => 'required|string|max:100'
		]);

		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))->get();

		$no_error = true;
		foreach ($photos as $photo) {
			$photo->title = $request['title'];
			$no_error |= $photo->save();
		}
		return $no_error ? 'true' : 'false';
	}



	/**
	 * @param Request $request
	 * @return string
	 */
	function setStar(Request $request)
	{

		$request->validate([
			'photoIDs' => 'required|string',
		]);

		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))->get();

		$no_error = true;
		foreach ($photos as $photo) {
			$photo->star = ($photo->star != 1) ? 1 : 0;
			$no_error |= $photo->save();
		}
		return $no_error ? 'true' : 'false';
	}



	/**
	 * @param Request $request
	 * @return string
	 */
	function setDescription(Request $request)
	{

		$request->validate([
			'photoID'     => 'required|string',
			'description' => 'string|nullable'
		]);

		$photo = Photo::find($request['photoID']);

		// Photo not found?
		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
			return 'false';
		}

		$photo->description = $request['description'];
		return $photo->save() ? 'true' : 'false';
	}



	/**
	 * @param Request $request
	 * @return string
	 */
	function setPublic(Request $request)
	{

		$request->validate([
			'photoID' => 'required|string'
		]);

		$photo = Photo::find($request['photoID']);

		// Photo not found?
		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
			return 'false';
		}

		$photo->public = $photo->public != 1 ? 1 : 0;
		return $photo->save() ? 'true' : 'false';
	}



	/**
	 * @param Request $request
	 * @return string
	 */
	function setTags(Request $request)
	{

		$request->validate([
			'photoIDs' => 'required|string',
			'tags'     => 'string|nullable'
		]);

		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))->get();

		$no_error = true;
		foreach ($photos as $photo) {
			$photo->tags = $request['tags'];
			$no_error &= $photo->save();
		}
		return $no_error ? 'true' : 'false';
	}



	/**
	 * @param Request $request
	 * @return string
	 */
	function setAlbum(Request $request)
	{

		$request->validate([
			'photoIDs' => 'required|string',
			'albumID'  => 'required|string'
		]);

		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))->get();

		$albumID = $request['albumID'];

		$no_error = true;
		foreach ($photos as $photo) {
			$photo->album_id = ($albumID == '0') ? null : $albumID;
			$no_error &= $photo->save();
		}
		Album::reset_takestamp();
		return $no_error ? 'true' : 'false';
	}



	/**
	 * @param Request $request
	 * @return false|string
	 */
	function setLicense(Request $request)
	{

		$request->validate([
			'photoID' => 'required|string',
			'license' => 'required|string'
		]);

		$photo = Photo::find($request['photoID']);

		// Photo not found?
		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
			return 'false';
		}

		$licenses = [
			'none',
			'reserved',
			'CC0',
			'CC-BY',
			'CC-BY-ND',
			'CC-BY-SA',
			'CC-BY-ND',
			'CC-BY-NC-ND',
			'CC-BY-SA'
		];
		$found = false;
		$i = 0;
		while (!$found && $i < count($licenses)) {
			if ($licenses[$i] == $request['license']) {
				$found = true;
			}
			$i++;
		}
		if (!$found) {
			Logs::error(__METHOD__, __LINE__, 'wrong kind of license: '.$request['license']);
			return Response::error('wrong kind of license!');
		}

		$photo->license = $request['license'];
		return $photo->save() ? 'true' : 'false';
	}



	/**
	 * @param Request $request
	 * @return string
	 */
	function delete(Request $request)
	{

		$request->validate([
			'photoIDs' => 'required|string',
		]);

		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))->get();

		$no_error = true;
		foreach ($photos as $photo) {
			$no_error &= $photo->predelete();
			$no_error &= $photo->delete();
		}
		Album::reset_takestamp();
		return $no_error ? 'true' : 'false';
	}



	/**
	 * @param Request $request
	 * @return string
	 */
	function duplicate(Request $request)
	{
		$request->validate([
			'photoIDs' => 'required|string',
		]);

		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))->get();

		$no_error = true;
		foreach ($photos as $photo) {
			$duplicate = new Photo();
			$duplicate->title = $photo->title;
			$duplicate->description = $photo->description;
			$duplicate->url = $photo->url;
			$duplicate->tags = $photo->tags;
			$duplicate->public = $photo->public;
			$duplicate->type = $photo->type;
			$duplicate->width = $photo->width;
			$duplicate->height = $photo->height;
			$duplicate->size = $photo->size;
			$duplicate->iso = $photo->iso;
			$duplicate->aperture = $photo->aperture;
			$duplicate->make = $photo->make;
			$duplicate->model = $photo->model;
			$duplicate->lens = $photo->lens;
			$duplicate->shutter = $photo->shutter;
			$duplicate->focal = $photo->focal;
			$duplicate->latitude = $photo->latitude;
			$duplicate->longitude = $photo->longitude;
			$duplicate->altitude = $photo->altitude;
			$duplicate->takestamp = $photo->takestamp;
			$duplicate->star = $photo->star;
			$duplicate->thumbUrl = $photo->thumbUrl;
			$duplicate->thumb2x = $photo->thumb2x;
			$duplicate->album_id = $photo->album_id;
			$duplicate->checksum = $photo->checksum;
			$duplicate->medium = $photo->medium;
			$duplicate->medium2x = $photo->medium2x;
			$duplicate->small = $photo->small;
			$duplicate->small2x = $photo->small2x;
			$duplicate->owner_id = $photo->owner_id;
			$no_error &= $duplicate->save();
		}
		return $no_error ? 'true' : 'false';

	}



	/**
	 * @param Request $request
	 * @return StreamedResponse|void
	 */
	function getArchive(Request $request)
	{

		// Illicit chars
		$badChars = array_merge(
			array_map('chr', range(0, 31)),
			array(
				"<",
				">",
				":",
				'"',
				"/",
				"\\",
				"|",
				"?",
				"*"
			)
		);

		$request->validate([
			'photoID' => 'required|string',
			'KIND'    => 'nullable|string'
		]);

		$photo = Photo::with('album')->find($request['photoID']);

		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
			return abort(404);
		}

		if ($this->readAccessFunctions->photo($photo)) {

			$title = ($photo->title == '') ? 'Untitled' : str_replace($badChars, '', $photo->title);

			// determine the file based on given size
			switch ($request['kind']) {
				case 'MEDIUM':
					$filepath = Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM').$photo->url;
					$kind = '-MQ-'.$photo->medium;
					break;
				case 'SMALL':
					$filepath = Config::get('defines.dirs.LYCHEE_UPLOADS_SMALL').$photo->url;
					$kind = '-LQ-'.$photo->small;
					break;
				default:
					$filepath = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$photo->url;
					$kind = '-HQ-'.$photo->width.'x'.$photo->height;
			}

			// Check the file actually exists
			if (!file_exists($filepath)) {
				Logs::error(__METHOD__, __LINE__, 'File is missing: '.$filepath.' ('.$title.')');
				return abort(404);
			}

			$response = new StreamedResponse(function () use ($title, $kind, $filepath) {

				$opt = array(
					'largeFileSize' => 100 * 1024 * 1024,
					'enableZip64'   => true,
					'send_headers'  => true,
				);

				$zip = new ZipStream($title.'.zip', $opt);

				// Get extension of image
				$extension = Helpers::getExtension($filepath, false);

				// Set title for photo
				$zip->addFileFromPath($title.$kind.$extension, $filepath);

				# finish the zip stream
				$zip->finish();

			});

			return $response;

		}

		Logs::error(__METHOD__, __LINE__, 'Accessing non public photo: '.$photo->id);
		return abort(403);

	}
}
