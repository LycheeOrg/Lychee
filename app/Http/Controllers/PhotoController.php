<?php

namespace App\Http\Controllers;

use App\Album;
use App\Logs;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class PhotoController extends Controller
{
	/**
	 * @var PhotoFunctions
	 */
	private $photoFunctions;



	/**
	 * @param PhotoFunctions $photoFunctions
	 */
	public function __construct(PhotoFunctions $photoFunctions)
	{
		$this->photoFunctions = $photoFunctions;
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

		$return = $photo->prepareData();
		if ((!Session::get('login') && $return['public'] == '1') ||
			(Session::get('login')) ||
			SessionController::checkAccess($request, $photo->album_id) === 1) {
			$return['original_album'] = $return['album'];
			$return['album'] = $request['albumID'];
			return $return;
		}

		Logs::error(__METHOD__, __LINE__, 'Accessing non public photo: '.$photo->id);
		return 'false';
	}



	/**
	 * @param Request $request
	 * @return array
	 */
	function getRandom(Request $request)
	{


		// here we need to refine.

		$photo = Photo::where('star', '=', 1)->inRandomOrder()->first();

		if ($photo == null) {
			return Response::error('no pictures found!');
		}

		$return = array();
		$return['thumb'] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB').$photo->thumbUrl;
		if ($photo->medium == '1') {
			$return['url'] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_MEDIUM').$photo->url;
		}
		else {
			$return['url'] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_BIG').$photo->url;
		}

		return $return;
	}



	/**
	 * @param Request $request
	 * @return false|string
	 * @throws \ImagickException
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

		$id = Session::get('UserID');

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
			$duplicate->album_id = $photo->album_id;
			$duplicate->checksum = $photo->checksum;
			$duplicate->medium = $photo->medium;
			$duplicate->small = $photo->small;
			$duplicate->owner_id = $photo->owner_id;
			$no_error &= $duplicate->save();
		}
		return $no_error ? 'true' : 'false';

	}
}
