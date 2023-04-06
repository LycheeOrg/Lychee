<?php

namespace App\Http\Controllers;

use App\Actions\Photo\Archive;
use App\Actions\Photo\Delete;
use App\Actions\Photo\Duplicate;
use App\Actions\User\Notify;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Factories\AlbumFactory;
use App\Http\Requests\Photo\AddPhotoRequest;
use App\Http\Requests\Photo\ArchivePhotosRequest;
use App\Http\Requests\Photo\ClearSymLinkRequest;
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
use App\Http\Requests\Photo\SetPhotoUploadDateRequest;
use App\Http\Resources\Models\PhotoResource;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\UploadedFile;
use App\Jobs\ProcessImageJob;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\Models\Photo;
use App\SmartAlbums\StarredAlbum;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PhotoController extends Controller
{
	/**
	 * @param SymLinkFunctions $symLinkFunctions
	 * @param AlbumFactory     $albumFactory
	 */
	public function __construct(
		private SymLinkFunctions $symLinkFunctions,
		private AlbumFactory $albumFactory
	) {
	}

	/**
	 * Given a photoID returns a photo.
	 *
	 * @param GetPhotoRequest $request
	 *
	 * @return PhotoResource
	 */
	public function get(GetPhotoRequest $request): PhotoResource
	{
		return PhotoResource::make($request->photo());
	}

	/**
	 * Returns a random public photo (starred)
	 * This is used in the Frame Controller.
	 *
	 * @return PhotoResource
	 *
	 * @throws ModelNotFoundException
	 * @throws InternalLycheeException
	 * @throws \InvalidArgumentException
	 *
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function getRandom(): PhotoResource
	{
		// PHPStan does not understand that `firstOrFail` returns `Photo`, but assumes that it returns `Model`
		// @phpstan-ignore-next-line
		return PhotoResource::make(StarredAlbum::getInstance()
			->photos()
			->inRandomOrder()
			->firstOrFail());
	}

	/**
	 * Adds a photo given an AlbumID.
	 *
	 * @param AddPhotoRequest $request
	 *
	 * @return PhotoResource|JsonResponse
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function add(AddPhotoRequest $request): PhotoResource|JsonResponse
	{
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
		$uploadedFile = new UploadedFile($request->uploadedFile());
		$processableFile = new ProcessableJobFile(
			$uploadedFile->getOriginalExtension(),
			$uploadedFile->getOriginalBasename()
		);
		$processableFile->write($uploadedFile->read());

		$uploadedFile->close();
		$uploadedFile->delete();
		$processableFile->close();
		// End of work-around

		if (Configs::getValueAsBool('use_job_queues')) {
			ProcessImageJob::dispatch($processableFile, $request->album());

			return new JsonResponse(null, 201);
		}

		$job = new ProcessImageJob($processableFile, $request->album());
		$photo = $job->handle($this->albumFactory);
		$isNew = $photo->created_at->toIso8601String() === $photo->updated_at->toIso8601String();

		return PhotoResource::make($photo)->setStatus($isNew ? 201 : 200);
	}

	/**
	 * Change the title of a photo.
	 *
	 * @param SetPhotosTitleRequest $request
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setTitle(SetPhotosTitleRequest $request): void
	{
		$title = $request->title();
		/** @var Photo $photo */
		foreach ($request->photos() as $photo) {
			$photo->title = $title;
			$photo->save();
		}
	}

	/**
	 * Set the is-starred attribute of the given photos.
	 *
	 * @param SetPhotosStarredRequest $request
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setStar(SetPhotosStarredRequest $request): void
	{
		/** @var Photo $photo */
		foreach ($request->photos() as $photo) {
			$photo->is_starred = $request->isStarred();
			$photo->save();
		}
	}

	/**
	 * Set the description of a photo.
	 *
	 * @param SetPhotoDescriptionRequest $request
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setDescription(SetPhotoDescriptionRequest $request): void
	{
		$request->photo()->description = $request->description();
		$request->photo()->save();
	}

	/**
	 * Sets the `is_public` attribute of the given photo.
	 *
	 * We do not advise the use of this and would rather see people use albums
	 * visibility.
	 * This would highly simplify the code if we remove this.
	 * Do we really want to keep it ?
	 *
	 * @param SetPhotoPublicRequest $request
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setPublic(SetPhotoPublicRequest $request): void
	{
		$request->photo()->is_public = $request->isPublic();
		$request->photo()->save();
	}

	/**
	 * Set the tags of a photo.
	 *
	 * @param SetPhotosTagsRequest $request
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setTags(SetPhotosTagsRequest $request): void
	{
		$tags = $request->tags();

		/** @var Photo $photo */
		foreach ($request->photos() as $photo) {
			if ($request->shallOverride) {
				$photo->tags = $tags;
			} else {
				$photo->tags = array_unique(array_merge($photo->tags, $tags));
			}
			$photo->save();
		}
	}

	/**
	 * Moves the photos to an album.
	 *
	 * @param MovePhotosRequest $request
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setAlbum(MovePhotosRequest $request): void
	{
		$notify = new Notify();
		$album = $request->album();

		/** @var Photo $photo */
		foreach ($request->photos() as $photo) {
			$photo->album_id = $album?->id;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$photo->setRelation('album', $album);
			if ($album !== null) {
				$photo->owner_id = $album->owner_id;
			}
			$photo->save();
			$notify->do($photo);
		}
	}

	/**
	 * Sets the license of the photo.
	 *
	 * @param SetPhotoLicenseRequest $request
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setLicense(SetPhotoLicenseRequest $request): void
	{
		$request->photo()->license = $request->license();
		$request->photo()->save();
	}

	/**
	 * Sets the license of the photo.
	 *
	 * @param SetPhotoUploadDateRequest $request
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setUploadDate(SetPhotoUploadDateRequest $request): void
	{
		$request->photo()->created_at = $request->requestDate();
		$request->photo()->save();
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
	 * @throws MediaFileOperationException
	 */
	public function delete(DeletePhotosRequest $request, Delete $delete): void
	{
		$fileDeleter = $delete->do($request->photoIDs());
		App::terminating(fn () => $fileDeleter->do());
	}

	/**
	 * Duplicates a set of photos.
	 * Only the SQL entry is duplicated for space reason.
	 *
	 * @param DuplicatePhotosRequest $request
	 * @param Duplicate              $duplicate
	 *
	 * @return JsonResponse the collection of duplicated photos
	 *
	 * @throws ModelDBException
	 */
	public function duplicate(DuplicatePhotosRequest $request, Duplicate $duplicate): JsonResponse
	{
		$duplicates = $duplicate->do($request->photos(), $request->album());

		return PhotoResource::collection($duplicates)->toResponse($request)->setStatusCode(201);
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
		return $archive->do($request->photos(), $request->sizeVariant());
	}

	/**
	 * GET to manually clear the symlinks.
	 *
	 * @param ClearSymLinkRequest $request
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 * @throws LycheeException
	 */
	public function clearSymLink(ClearSymLinkRequest $request): void
	{
		$this->symLinkFunctions->clearSymLink();
	}
}
