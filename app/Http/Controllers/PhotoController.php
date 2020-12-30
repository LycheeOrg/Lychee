<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use AccessControl;
use App\Actions\Album\UpdateTakestamps;
use App\Actions\Albums\Extensions\PublicIds;
use App\Actions\Photo\Create;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\Save;
use App\Actions\Photo\Prepare;
use App\Actions\Photo\Random;
use App\Assets\Helpers;
use App\Factories\AlbumFactory;
use App\Http\Requests\AlbumRequests\AlbumIDRequest;
use App\Http\Requests\PhotoRequests\PhotoIDRequest;
use App\Http\Requests\PhotoRequests\PhotoIDsRequest;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class PhotoController extends Controller
{
	use PublicIds;
	use Constants;
	use Save;

	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @param SymLinkFunctions $symLinkFunctions
	 */
	public function __construct(
		SymLinkFunctions $symLinkFunctions
	) {
		$this->symLinkFunctions = $symLinkFunctions;
	}

	/**
	 * Given a photoID returns the data of the photo.
	 *
	 * @param Request $request
	 *
	 * @return array|string
	 */
	public function get(PhotoIDRequest $request, Prepare $prepare)
	{
		$photo = Photo::with('album')->findOrFail($request['photoID']);

		return $prepare->do($photo);
	}

	/**
	 * Return a random public photo (starred)
	 * This is used in the Frame Controller.
	 *
	 * @return string
	 */
	public function getRandom(Random $random)
	{
		return $random->do();
	}

	/**
	 * Add a function given an AlbumID.
	 *
	 * @param Request $request
	 *
	 * @return false|string
	 */
	public function add(AlbumIDRequest $request, Create $create)
	{
		$request->validate([
			'0' => 'required',
		]);

		if (!$request->hasfile('0')) {
			return Response::error('missing files');
		}

		// Only process the first photo in the array
		$file = $request->file('0');

		$nameFile = [];
		$nameFile['name'] = $file->getClientOriginalName();
		$nameFile['type'] = $file->getMimeType();
		$nameFile['tmp_name'] = $file->getPathName();

		return $create->add($nameFile, $request['albumID']);
	}

	/**
	 * Change the title of a photo.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setTitle(PhotoIDsRequest $request)
	{
		$request->validate([
			'title' => 'required|string|max:100',
		]);

		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))
			->get();

		$no_error = true;
		foreach ($photos as $photo) {
			$photo->title = $request['title'];
			$no_error |= $photo->save();
		}

		return $no_error ? 'true' : 'false';
	}

	/**
	 * Set if a photo is a favorite.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setStar(PhotoIDsRequest $request)
	{
		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))
			->get();

		$no_error = true;
		foreach ($photos as $photo) {
			$photo->star = ($photo->star != 1) ? 1 : 0;
			$no_error |= $photo->save();
		}

		return $no_error ? 'true' : 'false';
	}

	/**
	 * Set the description of a photo.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setDescription(PhotoIDRequest $request)
	{
		$request->validate([
			'description' => 'string|nullable',
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
	 * Define if a photo is public.
	 * We do not advise the use of this and would rather see people use albums visibility
	 * This would highly simplify the code if we remove this. Do we really want to keep it ?
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setPublic(PhotoIDRequest $request)
	{
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
	 * Set the tags of a photo.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setTags(PhotoIDsRequest $request)
	{
		$request->validate([
			'tags' => 'string|nullable',
		]);

		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))
			->get();

		$no_error = true;
		foreach ($photos as $photo) {
			$photo->tags = ($request['tags'] !== null ? $request['tags'] : '');
			$no_error &= $photo->save();
		}

		return $no_error ? 'true' : 'false';
	}

	/**
	 * Define the album of a photo.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setAlbum(PhotoIDsRequest $request, UpdateTakestamps $updateTakestamps)
	{
		$request->validate([
			'albumID' => 'required|string',
		]);

		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))
			->get();

		$albumID = $request['albumID'];

		$album = null;
		if ($albumID !== '0') {
			// just to be sure to handle ownership changes in the process.
			$album = Album::findOrFail($albumID);
		}

		$no_error = true;
		$takestamp = [];
		foreach ($photos as $photo) {
			$oldAlbumID = $photo->album_id;

			$photo->album_id = ($albumID == '0') ? null : $albumID;
			if ($album !== null) {
				$photo->owner_id = $album->owner_id;
				$takestamp[] = $photo->takestamp;
			}
			$no_error &= $photo->save();

			if ($oldAlbumID !== null) {
				$oldAlbum = Album::find($oldAlbumID);
				if ($oldAlbum === null) {
					Logs::error(
						__METHOD__,
						__LINE__,
						'Could not find an album'
					);
					$no_error = false;
				}
				$no_error &= $updateTakestamps->singleAndSave($oldAlbum);
			}
		}
		if ($album !== null) {
			$no_error &= $updateTakestamps->singleAndSave($album);
		}

		return $no_error ? 'true' : 'false';
	}

	/**
	 * Define the license of the photo.
	 *
	 * @param Request $request
	 *
	 * @return false|string
	 */
	public function setLicense(PhotoIDRequest $request)
	{
		$request->validate([
			'license' => 'required|string',
		]);

		$photo = Photo::find($request['photoID']);

		// Photo not found?
		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');

			return 'false';
		}

		$licenses = Helpers::get_all_licenses();

		$found = false;
		$i = 0;
		while (!$found && $i < count($licenses)) {
			if ($licenses[$i] == $request['license']) {
				$found = true;
			}
			$i++;
		}
		if (!$found) {
			Logs::error(
				__METHOD__,
				__LINE__,
				'License not recognised: ' . $request['license']
			);

			return Response::error('License not recognised!');
		}

		$photo->license = $request['license'];

		return $photo->save() ? 'true' : 'false';
	}

	/**
	 * Delete a photo.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function delete(PhotoIDsRequest $request, UpdateTakestamps $updateTakestamps)
	{
		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))
			->get();

		$no_error = true;
		$albums = [];
		$takestamp = [];

		foreach ($photos as $photo) {
			$no_error &= $photo->predelete();

			if ($photo->album_id !== null) {
				$albums[] = $photo->album;
				$takestamp[] = $photo->takestamp;
			}

			$no_error &= $photo->delete();
		}

		// TODO: ideally we would like to avoid duplicates here...
		for ($i = 0; $i < count($albums); $i++) {
			$no_error &= $updateTakestamps->singleAndSave($albums[$i]);
		}

		return $no_error ? 'true' : 'false';
	}

	/**
	 * Duplicate a photo.
	 * Only the SQL entry is duplicated for space reason.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function duplicate(PhotoIDsRequest $request, AlbumFactory $albumFactory, UpdateTakestamps $updateTakestamps)
	{
		$request->validate([
			'albumID' => 'string',
		]);

		$photos = Photo::whereIn('id', explode(',', $request['photoIDs']))
			->get();

		$duplicate = null;
		foreach ($photos as $photo) {
			$duplicate = new Photo();
			$duplicate->id = Helpers::generateID();
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
			$duplicate->imgDirection = $photo->imgDirection;
			$duplicate->location = $photo->location;
			$duplicate->takestamp = $photo->takestamp;
			$duplicate->star = $photo->star;
			$duplicate->thumbUrl = $photo->thumbUrl;
			$duplicate->thumb2x = $photo->thumb2x;
			$duplicate->album_id = isset($request['albumID']) ? $request['albumID'] : $photo->album_id;
			$duplicate->checksum = $photo->checksum;
			$duplicate->medium = $photo->medium;
			$duplicate->medium2x = $photo->medium2x;
			$duplicate->small = $photo->small;
			$duplicate->small2x = $photo->small2x;
			$duplicate->owner_id = $photo->owner_id;
			$duplicate->livePhotoContentID = $photo->livePhotoContentID;
			$duplicate->livePhotoUrl = $photo->livePhotoUrl;
			$duplicate->livePhotoChecksum = $photo->livePhotoChecksum;
			$this->save($duplicate);
		}
		if ($duplicate->album_id != null) {
			$parent = $albumFactory->make($duplicate->album_id);
			$updateTakestamps->singleAndSave($parent);
		}

		return 'true';
	}

	/**
	 * extract the file names.
	 *
	 * @param $photoID
	 * @param $request
	 *
	 * @return array|null
	 */
	public function extract_names($photoID, $request)
	{
		// Illicit chars
		$badChars = array_merge(
			array_map('chr', range(0, 31)),
			[
				'<',
				'>',
				':',
				'"',
				'/',
				'\\',
				'|',
				'?',
				'*',
			]
		);

		$photo = Photo::with('album')->find($photoID);

		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');

			return null;
		}

		if (!AccessControl::is_current_user($photo->owner_id)) {
			if ($photo->album_id !== null) {
				if (!$photo->album->is_downloadable()) {
					return null;
				}
			} else {
				if (Configs::get_value('downloadable', '0') === '0') {
					return null;
				}
			}
		}

		$title = str_replace($badChars, '', $photo->title);
		if ($title === '') {
			$title = 'Untitled';
		}

		$prefix_path = $photo->type == 'raw' ? 'raw/' : 'big/';
		// determine the file based on given size
		switch ($request['kind']) {
			case 'FULL':
				$path = $prefix_path . $photo->url;
				$kind = '';
				break;
			case 'LIVEPHOTOVIDEO':
				$path = $prefix_path . $photo->livePhotoUrl;
				$kind = '';
				break;
			case 'MEDIUM2X':
				if ($this->isVideo($photo) === false) {
					$fileName = $photo->url;
				} else {
					$fileName = $photo->thumbUrl;
				}
				$fileName2x = explode('.', $fileName);
				$fileName2x = $fileName2x[0] . '@2x.' . $fileName2x[1];
				$path = 'medium/' . $fileName2x;
				$kind = '-' . $photo->medium2x;
				break;
			case 'MEDIUM':
				if ($this->isVideo($photo) === false) {
					$path = 'medium/' . $photo->url;
				} else {
					$path = 'medium/' . $photo->thumbUrl;
				}
				$kind = '-' . $photo->medium;
				break;
			case 'SMALL2X':
				if ($this->isVideo($photo) === false) {
					$fileName = $photo->url;
				} else {
					$fileName = $photo->thumbUrl;
				}
				$fileName2x = explode('.', $fileName);
				$fileName2x = $fileName2x[0] . '@2x.' . $fileName2x[1];
				$path = 'small/' . $fileName2x;
				$kind = '-' . $photo->small2x;
				break;
			case 'SMALL':
				if ($this->isVideo($photo) === false) {
					$path = 'small/' . $photo->url;
				} else {
					$path = 'small/' . $photo->thumbUrl;
				}
				$kind = '-' . $photo->small;
				break;
			case 'THUMB2X':
				$fileName2x = explode('.', $photo->thumbUrl);
				$fileName2x = $fileName2x[0] . '@2x.' . $fileName2x[1];
				$path = 'thumb/' . $fileName2x;
				$kind = '-400x400';
				break;
			case 'THUMB':
				$path = 'thumb/' . $photo->thumbUrl;
				$kind = '-200x200';
				break;
			default:
				Logs::error(
					__METHOD__,
					__LINE__,
					'Invalid kind ' . $request['kind']
				);

				return null;
		}

		$fullpath = Storage::path($path);
		// Check the file actually exists
		if (!Storage::exists($path)) {
			Logs::error(__METHOD__, __LINE__, 'File is missing: ' . $fullpath . ' (' . $title . ')');

			return null;
		}

		// Get extension of image
		$extension = '';
		if ($photo->type != 'raw') {
			$extension = Helpers::getExtension($fullpath, false);
		}

		return [$title, $kind, $extension, $fullpath];
	}

	/**
	 * Return the archive of pictures or just a picture if only one.
	 *
	 * @param Request $request
	 *
	 * @return StreamedResponse|Response|string|void
	 */
	public function getArchive(PhotoIDsRequest $request)
	{
		if (Storage::getDefaultDriver() === 's3') {
			Logs::error(__METHOD__, __LINE__, 'getArchive not implemented for S3');

			return 'false';
		}

		$request->validate([
			'kind' => 'nullable|string',
		]);

		$photoIDs = explode(',', $request['photoIDs']);

		if (count($photoIDs) === 1) {
			$ret = $this->extract_names($photoIDs[0], $request);
			if ($ret === null) {
				return abort(404);
			}

			list($title, $kind, $extension, $url) = $ret;

			// Set title for photo
			$file = $title . $kind . $extension;

			$response = new BinaryFileResponse($url);
			$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file);
		} else {
			$response = new StreamedResponse(function () use ($request, $photoIDs) {
				$options = new \ZipStream\Option\Archive();
				$options->setEnableZip64(Configs::get_value('zip64', '1') === '1');
				$zip = new ZipStream(null, $options);

				$files = [];
				foreach ($photoIDs as $photoID) {
					$ret = $this->extract_names($photoID, $request);
					if ($ret == null) {
						return abort(404);
					}

					list($title, $kind, $extension, $url) = $ret;

					// Set title for photo
					$file = $title . $kind . $extension;
					// Check for duplicates
					if (!empty($files)) {
						$i = 1;
						$tmp_file = $file;
						while (in_array($tmp_file, $files)) {
							// Set new title for photo
							$tmp_file = $title . $kind . '-' . $i . $extension;
							$i++;
						}
						$file = $tmp_file;
					}
					// Add to array
					$files[] = $file;

					// Reset the execution timeout for every iteration.
					set_time_limit(ini_get('max_execution_time'));

					$zip->addFileFromPath($file, $url);
				} // foreach ($photoIDs)

				// finish the zip stream
				$zip->finish();
			});

			// Set file type and destination
			$response->headers->set('Content-Type', 'application/x-zip');
			$disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'Photos.zip');
			$response->headers->set('Content-Disposition', $disposition);
		}

		// Disable caching
		$response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
		$response->headers->set('Pragma', 'no-cache');
		$response->headers->set('Expires', '0');

		return $response;
	}

	/**
	 * GET to manually clear the symlinks.
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function clearSymLink()
	{
		return $this->symLinkFunctions->clearSymLink();
	}
}
