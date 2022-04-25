<?php

namespace App\Http\Controllers;

use App\Actions\Photo\Archive;
use App\Actions\Photo\Create;
use App\Actions\Photo\Delete;
use App\Actions\Photo\Duplicate;
use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Actions\Photo\Random;
use App\Actions\Photo\SetAlbum;
use App\Actions\Photo\SetDescription;
use App\Actions\Photo\SetLicense;
use App\Actions\Photo\SetPublic;
use App\Actions\Photo\SetStar;
use App\Actions\Photo\SetTags;
use App\Actions\Photo\SetTitle;
use App\Actions\Photo\Strategies\ImportMode;
use App\Exceptions\FolderIsNotWritable;
use App\Exceptions\JsonError;
use App\Facades\Helpers;
use App\Http\Requests\AlbumRequests\AlbumIDRequest;
use App\Http\Requests\PhotoRequests\PhotoIDRequest;
use App\Http\Requests\PhotoRequests\PhotoIDsRequest;
use App\Image\TemporaryLocalFile;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use App\Rules\ModelIDRule;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PhotoController extends Controller
{
	private SymLinkFunctions $symLinkFunctions;

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
	 * @param PhotoIDRequest $request
	 *
	 * @return Photo
	 */
	public function get(PhotoIDRequest $request): Photo
	{
		return Photo::query()
			->with(['size_variants', 'size_variants.sym_links'])
			->findOrFail($request['photoID']);
	}

	/**
	 * Return a random public photo (starred)
	 * This is used in the Frame Controller.
	 *
	 * @param Random $random
	 *
	 * @return Photo
	 *
	 * @throws JsonError
	 */
	public function getRandom(Random $random): Photo
	{
		return $random->do();
	}

	/**
	 * Add a function given an AlbumID.
	 *
	 * @param AlbumIDRequest $request
	 *
	 * @return Photo
	 *
	 * @throws FolderIsNotWritable
	 * @throws JsonError
	 */
	public function add(AlbumIDRequest $request): Photo
	{
		$request->validate(['0' => 'required|file']);
		// Only process the first photo in the array
		/** @var UploadedFile $file */
		$file = $request->file('0');
		$sourceFileInfo = SourceFileInfo::createByUploadedFile($file);

		// This code is a nasty work-around which should not exist.
		// PHP stores a temporary copy of the uploaded file without a file
		// extension.
		// Unfortunately, most of our methods pass around absolute file paths
		// instead of proper `File` object.
		// During the process we have a lot of code which tries to
		// re-determine the MIME type of the file based on the file path.
		// This is not only inefficient, but the original MIME type (of the
		// uploaded file) gets lost on the way.
		// As a work-around we store the uploaded file with a file extension.
		// Unfortunately, we cannot simply re-name the file, because this
		// might break due to permission problems for certain installation
		// if the temporarily uploaded file is stored in the system-global
		// temporary directory below another mount point or another Docker
		// image than the Lychee installation.
		// Hence, we must make a deep copy.
		// TODO: Remove this code again, if all other TODOs regarding MIME and file handling are properly refactored and we have stopped using absolute file paths as the least common denominator to pass around files.
		$uploadedFile = $sourceFileInfo->getFile();
		$copiedFile = new TemporaryLocalFile($sourceFileInfo->getOriginalExtension());
		$copiedFile->write($uploadedFile->read());
		$uploadedFile->close();
		$uploadedFile->delete();
		// Reset source file info to the new copy
		$sourceFileInfo = SourceFileInfo::createByTempFile(
			$sourceFileInfo->getOriginalName(),
			$sourceFileInfo->getOriginalExtension(),
			$copiedFile
		);
		// End of work-around

		$albumID = $request['albumID'];

		// As the file has been uploaded, the (temporary) source file shall be
		// deleted
		$create = new Create(new ImportMode(
			true,
			Configs::get_value('skip_duplicates', '0') === '1'
		));

		return $create->add($sourceFileInfo, $albumID);
	}

	/**
	 * Change the title of a photo.
	 *
	 * @param PhotoIDsRequest $request
	 * @param SetTitle        $setTitle
	 *
	 * @return string
	 */
	public function setTitle(PhotoIDsRequest $request, SetTitle $setTitle): string
	{
		$request->validate(['title' => 'required|string|max:100']);

		return $setTitle->do(explode(',', $request['photoIDs']), $request['title']) ? 'true' : 'false';
	}

	/**
	 * Set if a photo is a favorite.
	 *
	 * @param PhotoIDsRequest $request
	 * @param SetStar         $setStar
	 *
	 * @return string
	 */
	public function setStar(PhotoIDsRequest $request, SetStar $setStar): string
	{
		return $setStar->do(explode(',', $request['photoIDs'])) ? 'true' : 'false';
	}

	/**
	 * Set the description of a photo.
	 *
	 * @param PhotoIDRequest $request
	 * @param SetDescription $setDescription
	 *
	 * @return string
	 */
	public function setDescription(PhotoIDRequest $request, SetDescription $setDescription): string
	{
		$request->validate(['description' => 'string|nullable']);

		return $setDescription->do($request['photoID'], $request['description'] ?? '') ? 'true' : 'false';
	}

	/**
	 * Define if a photo is public.
	 * We do not advise the use of this and would rather see people use albums visibility
	 * This would highly simplify the code if we remove this. Do we really want to keep it ?
	 *
	 * @param PhotoIDRequest $request
	 * @param SetPublic      $setPublic
	 *
	 * @return string
	 */
	public function setPublic(PhotoIDRequest $request, SetPublic $setPublic): string
	{
		return $setPublic->do($request['photoID']) ? 'true' : 'false';
	}

	/**
	 * Set the tags of a photo.
	 *
	 * @param PhotoIDsRequest $request
	 * @param SetTags         $setTags
	 *
	 * @return string
	 */
	public function setTags(PhotoIDsRequest $request, SetTags $setTags): string
	{
		$request->validate(['tags' => 'string|nullable']);

		return $setTags->do(explode(',', $request['photoIDs']), $request['tags'] ?? '') ? 'true' : 'false';
	}

	/**
	 * Define the album of a photo.
	 *
	 * @param PhotoIDsRequest $request
	 * @param SetAlbum        $setAlbum
	 *
	 * @return string
	 */
	public function setAlbum(PhotoIDsRequest $request, SetAlbum $setAlbum): string
	{
		$request->validate(['albumID' => ['present', new ModelIDRule()]]);

		return $setAlbum->execute(explode(',', $request['photoIDs']), $request['albumID']) ? 'true' : 'false';
	}

	/**
	 * Sets the license of the photo.
	 *
	 * @param PhotoIDRequest $request
	 * @param SetLicense     $setLicense
	 *
	 * @return IlluminateResponse
	 */
	public function setLicense(PhotoIDRequest $request, SetLicense $setLicense): IlluminateResponse
	{
		$licenses = Helpers::get_all_licenses();
		$request->validate([
			'license' => [
				'string',
				'required',
				Rule::in($licenses),
			],
		]);

		$setLicense->do($request['photoID'], $request['license']);

		return response()->noContent();
	}

	/**
	 * Delete one or more photos.
	 *
	 * @param PhotoIDsRequest $request
	 * @param Delete          $delete
	 *
	 * @return IlluminateResponse
	 */
	public function delete(PhotoIDsRequest $request, Delete $delete): IlluminateResponse
	{
		$fileDeleter = $delete->do(explode(',', $request['photoIDs']));
		App::terminating(fn () => $fileDeleter->do());

		return response()->noContent();
	}

	/**
	 * Duplicates a set of photos.
	 * Only the SQL entry is duplicated for space reason.
	 *
	 * @param PhotoIDsRequest $request
	 * @param Duplicate       $duplicate
	 *
	 * @return Photo|Collection the duplicated photo or collection of duplicated photos
	 */
	public function duplicate(PhotoIDsRequest $request, Duplicate $duplicate)
	{
		$request->validate(['albumID' => ['present', new ModelIDRule()]]);
		$duplicates = $duplicate->do(explode(',', $request['photoIDs']), $request['albumID']);

		return ($duplicates->count() === 1) ? $duplicates->first() : $duplicates;
	}

	/**
	 * Return the archive of pictures or just a picture if only one.
	 *
	 * @param PhotoIDsRequest $request
	 * @param Archive         $archive
	 *
	 * @return SymfonyResponse|string
	 */
	public function getArchive(PhotoIDsRequest $request, Archive $archive)
	{
		if (Storage::getDefaultDriver() === 's3') {
			Logs::error(__METHOD__, __LINE__, 'getArchive not implemented for S3');

			return 'false';
		}

		$request->validate([
			'kind' => 'nullable|string',
		]);

		$photoIDs = explode(',', $request['photoIDs']);

		$response = $archive->do($photoIDs, $request['kind']);

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
	public function clearSymLink(): string
	{
		return $this->symLinkFunctions->clearSymLink();
	}
}
