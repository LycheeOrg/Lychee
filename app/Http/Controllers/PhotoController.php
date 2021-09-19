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
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Facades\Helpers;
use App\Http\Requests\AlbumRequests\AlbumIDRequest;
use App\Http\Requests\PhotoRequests\PhotoIDRequest;
use App\Http\Requests\PhotoRequests\PhotoIDsRequest;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Logs;
use App\Models\Photo;
use App\Rules\ModelIDRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
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
	 *
	 * @throws ModelNotFoundException
	 */
	public function get(PhotoIDRequest $request): Photo
	{
		/* @noinspection PhpIncompatibleReturnTypeInspection */
		return Photo::query()
			->with(['size_variants_raw', 'size_variants_raw.sym_links'])
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
	 * @throws ModelNotFoundException
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
	 * @throws InsufficientFilesystemPermissions
	 * @throws ModelDBException
	 * @throws InvalidPropertyException
	 * @throws MediaFileOperationException
	 * @throws ExternalComponentMissingException
	 */
	public function add(AlbumIDRequest $request): Photo
	{
		$request->validate(['0' => 'required|file']);
		// Only process the first photo in the array
		/** @var UploadedFile $file */
		$file = $request->file('0');
		$sourceFileInfo = SourceFileInfo::createForUploadedFile($file);
		$albumID = $request['albumID'];

		if (empty($albumID)) {
			$albumID = null;
		} elseif (is_numeric($albumID)) {
			$albumID = intval($albumID);
		}
		// If the file has been uploaded, the (temporary) source file shall be
		// deleted
		$create = new Create(new ImportMode(
			is_uploaded_file($sourceFileInfo->getTmpFullPath())
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
	 *
	 * @throws ModelNotFoundException
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
	 *
	 * @throws ModelNotFoundException
	 */
	public function setAlbum(PhotoIDsRequest $request, SetAlbum $setAlbum): string
	{
		$request->validate(['albumID' => ['required', new ModelIDRule()]]);

		return $setAlbum->do(explode(',', $request['photoIDs']), $request['albumID']) ? 'true' : 'false';
	}

	/**
	 * Sets the license of the photo.
	 *
	 * @param PhotoIDRequest $request
	 * @param SetLicense     $setLicense
	 *
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 */
	public function setLicense(PhotoIDRequest $request, SetLicense $setLicense): void
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
	}

	/**
	 * Delete one or more photos.
	 *
	 * @param PhotoIDsRequest $request
	 * @param Delete          $delete
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function delete(PhotoIDsRequest $request, Delete $delete): void
	{
		$delete->do(explode(',', $request['photoIDs']));
	}

	/**
	 * Duplicates a set of photos.
	 * Only the SQL entry is duplicated for space reason.
	 *
	 * @param PhotoIDsRequest $request
	 * @param Duplicate       $duplicate
	 *
	 * @return Photo|Collection the duplicated photo or collection of duplicated photos
	 *
	 * @throws ModelDBException
	 */
	public function duplicate(PhotoIDsRequest $request, Duplicate $duplicate)
	{
		$request->validate(['albumID' => 'string']);
		$duplicates = $duplicate->do(explode(',', $request['photoIDs']), $request['albumID'] ? intval($request['albumID']) : null);

		return ($duplicates->count() === 1) ? $duplicates->first() : $duplicates;
	}

	/**
	 * Return the archive of pictures or just a picture if only one.
	 *
	 * @param PhotoIDsRequest $request
	 * @param Archive         $archive
	 *
	 * @return SymfonyResponse
	 */
	public function getArchive(PhotoIDsRequest $request, Archive $archive): SymfonyResponse
	{
		if (Storage::getDefaultDriver() === 's3') {
			Logs::error(__METHOD__, __LINE__, 'getArchive not implemented for S3');

			return new SymfonyResponse('getArchive not implemented for S3', SymfonyResponse::HTTP_NOT_IMPLEMENTED);
		}

		$request->validate([
			'kind' => [Rule::in(Archive::VARIANTS)],
		]);

		$photoIDs = explode(',', $request['photoIDs']);

		try {
			$response = $archive->do($photoIDs, $request['kind']);
		} catch (InvalidSizeVariantException $ignored) {
			// In theory Archive::do may throw this exception,
			// but will never do so, because we validated the input
			$response = new SymfonyResponse(null, SymfonyResponse::HTTP_NO_CONTENT);
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
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function clearSymLink(): void
	{
		$this->symLinkFunctions->clearSymLink();
	}
}
