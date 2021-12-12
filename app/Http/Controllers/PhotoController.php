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
use App\Contracts\LycheeException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthorizedException;
use App\Facades\AccessControl;
use App\Http\Requests\Photo\AddPhotoRequest;
use App\Http\Requests\Photo\ArchivePhotosRequest;
use App\Http\Requests\Photo\DeletePhotosRequest;
use App\Http\Requests\Photo\DuplicatePhotosRequest;
use App\Http\Requests\Photo\GetPhotoRequest;
use App\Http\Requests\Photo\MovePhotosRequest;
use App\Http\Requests\Photo\SetPhotoDescriptionRequest;
use App\Http\Requests\Photo\SetPhotoLicenseRequest;
use App\Http\Requests\Photo\SetPhotoPublicRequest;
use App\Http\Requests\Photo\SetPhotosStarredRequest;
use App\Http\Requests\Photo\SetPhotosTagsRequest;
use App\Http\Requests\Photo\SetPhotosTitleRequest;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
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
	 * Given a photoID returns a photo.
	 *
	 * @param GetPhotoRequest $request
	 *
	 * @return Photo
	 *
	 * @throws ModelNotFoundException
	 */
	public function get(GetPhotoRequest $request): Photo
	{
		/* @noinspection PhpIncompatibleReturnTypeInspection */
		return Photo::query()
			->with(['size_variants', 'size_variants.sym_links'])
			->findOrFail($request->photoID());
	}

	/**
	 * Returns a random public photo (starred)
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
	 * Adds a photo given an AlbumID.
	 *
	 * @param AddPhotoRequest $request
	 *
	 * @return Photo
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function add(AddPhotoRequest $request): Photo
	{
		$sourceFileInfo = SourceFileInfo::createForUploadedFile(
			$request->uploadedFile()
		);
		// If the file has been uploaded, the (temporary) source file shall be
		// deleted
		$create = new Create(new ImportMode(
			is_uploaded_file($sourceFileInfo->getTmpFullPath())
		));

		return $create->add($sourceFileInfo, $request->albumID());
	}

	/**
	 * Change the title of a photo.
	 *
	 * @param SetPhotosTitleRequest $request
	 * @param SetTitle              $setTitle
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setTitle(SetPhotosTitleRequest $request, SetTitle $setTitle): void
	{
		$setTitle->do($request->photoIDs(), $request->title());
	}

	/**
	 * Toggles the is-starred attribute of the given photos.
	 *
	 * @param SetPhotosStarredRequest $request
	 * @param SetStar                 $setStar
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setStar(SetPhotosStarredRequest $request, SetStar $setStar): void
	{
		$setStar->do($request->photoIDs());
	}

	/**
	 * Set the description of a photo.
	 *
	 * @param SetPhotoDescriptionRequest $request
	 * @param SetDescription             $setDescription
	 *
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 * @throws LycheeException
	 */
	public function setDescription(SetPhotoDescriptionRequest $request, SetDescription $setDescription): void
	{
		$setDescription->do($request->photoID(), $request->description());
	}

	/**
	 * Toggles the is-public attribute of the given photo.
	 *
	 * We do not advise the use of this and would rather see people use albums visibility
	 * This would highly simplify the code if we remove this. Do we really want to keep it ?
	 *
	 * @param SetPhotoPublicRequest $request
	 * @param SetPublic             $setPublic
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function setPublic(SetPhotoPublicRequest $request, SetPublic $setPublic): void
	{
		$setPublic->do($request->photoID);
	}

	/**
	 * Set the tags of a photo.
	 *
	 * @param SetPhotosTagsRequest $request
	 * @param SetTags              $setTags
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setTags(SetPhotosTagsRequest $request, SetTags $setTags): void
	{
		$setTags->do($request->photoIDs(), $request->tags());
	}

	/**
	 * Moves the photos to an album.
	 *
	 * @param MovePhotosRequest $request
	 * @param SetAlbum          $setAlbum
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function setAlbum(MovePhotosRequest $request, SetAlbum $setAlbum): void
	{
		$setAlbum->do($request->photoIDs(), $request->albumID());
	}

	/**
	 * Sets the license of the photo.
	 *
	 * @param SetPhotoLicenseRequest $request
	 * @param SetLicense             $setLicense
	 *
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 * @throws LycheeException
	 */
	public function setLicense(SetPhotoLicenseRequest $request, SetLicense $setLicense): void
	{
		$setLicense->do($request->photoID(), $request->license());
	}

	/**
	 * Delete one or more photos.
	 *
	 * @param DeletePhotosRequest $request
	 * @param Delete              $delete
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function delete(DeletePhotosRequest $request, Delete $delete): void
	{
		$delete->do($request->photoIDs());
	}

	/**
	 * Duplicates a set of photos.
	 * Only the SQL entry is duplicated for space reason.
	 *
	 * @param DuplicatePhotosRequest $request
	 * @param Duplicate              $duplicate
	 *
	 * @return Photo|Collection the duplicated photo or collection of duplicated photos
	 *
	 * @throws ModelDBException
	 */
	public function duplicate(DuplicatePhotosRequest $request, Duplicate $duplicate)
	{
		$duplicates = $duplicate->do($request->photoIDs(), $request->albumID());

		return ($duplicates->count() === 1) ? $duplicates->first() : $duplicates;
	}

	/**
	 * Return the archive of pictures or just a picture if only one.
	 *
	 * @param ArchivePhotosRequest $request
	 * @param Archive              $archive
	 *
	 * @return SymfonyResponse
	 *
	 * @throws LycheeException
	 */
	public function getArchive(ArchivePhotosRequest $request, Archive $archive): SymfonyResponse
	{
		return $archive->do($request->photoIDs(), $request->sizeVariant());
	}

	/**
	 * GET to manually clear the symlinks.
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 * @throws LycheeException
	 */
	public function clearSymLink(): void
	{
		if (!AccessControl::is_admin()) {
			throw new UnauthorizedException('Admin privileges required');
		}
		$this->symLinkFunctions->clearSymLink();
	}
}
