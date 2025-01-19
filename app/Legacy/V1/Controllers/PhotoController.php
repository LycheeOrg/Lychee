<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers;

use App\Actions\Photo\Archive;
use App\Actions\Photo\BaseArchive;
use App\Actions\Photo\Delete;
use App\Actions\Photo\Duplicate;
use App\Actions\Photo\Move;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Factories\AlbumFactory;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\UploadedFile;
use App\Jobs\ProcessImageJob;
use App\Legacy\V1\Requests\Photo\AddPhotoRequest;
use App\Legacy\V1\Requests\Photo\ArchivePhotosRequest;
use App\Legacy\V1\Requests\Photo\ClearSymLinkRequest;
use App\Legacy\V1\Requests\Photo\DeletePhotosRequest;
use App\Legacy\V1\Requests\Photo\DuplicatePhotosRequest;
use App\Legacy\V1\Requests\Photo\GetPhotoRequest;
use App\Legacy\V1\Requests\Photo\MovePhotosRequest;
use App\Legacy\V1\Requests\Photo\SetPhotoDescriptionRequest;
use App\Legacy\V1\Requests\Photo\SetPhotoLicenseRequest;
use App\Legacy\V1\Requests\Photo\SetPhotosStarredRequest;
use App\Legacy\V1\Requests\Photo\SetPhotosTagsRequest;
use App\Legacy\V1\Requests\Photo\SetPhotosTitleRequest;
use App\Legacy\V1\Requests\Photo\SetPhotoUploadDateRequest;
use App\Legacy\V1\Resources\Models\PhotoResource;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class PhotoController extends Controller
{
	/**
	 * @param SymLinkFunctions $symLinkFunctions
	 * @param AlbumFactory     $albumFactory
	 */
	public function __construct(
		private SymLinkFunctions $symLinkFunctions,
		private AlbumFactory $albumFactory,
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
	 * Returns a random photo (from the configured album).
	 * Only photos with enough access rights are included.
	 * This is used in the Frame Controller.
	 *
	 * @param PhotoQueryPolicy $photoQueryPolicy
	 *
	 * @return PhotoResource
	 *
	 * @throws ModelNotFoundException
	 * @throws InternalLycheeException
	 * @throws \InvalidArgumentException
	 *
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function getRandom(PhotoQueryPolicy $photoQueryPolicy): PhotoResource
	{
		$randomAlbumId = Configs::getValueAsString('random_album_id');

		if ($randomAlbumId === '') {
			// @codeCoverageIgnoreStart
			$query = $photoQueryPolicy->applySearchabilityFilter(
				query: Photo::query()->with(['album', 'size_variants', 'size_variants.sym_links']),
				origin: null,
				include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_frame'));
		// @codeCoverageIgnoreEnd
		} else {
			$query = $this->albumFactory->findAbstractAlbumOrFail($randomAlbumId)
									 ->photos()
									 ->with(['album', 'size_variants', 'size_variants.sym_links']);
		}

		$num = $query->count() - 1;

		return PhotoResource::make($query->skip(rand(0, $num))->firstOrFail());
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
			ProcessImageJob::dispatch($processableFile, $request->album(), $request->fileLastModifiedTime());

			return new JsonResponse(null, 201);
		}

		$job = new ProcessImageJob($processableFile, $request->album(), $request->fileLastModifiedTime());
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
	 */
	public function setDescription(SetPhotoDescriptionRequest $request): void
	{
		$request->photo()->description = $request->description();
		$request->photo()->save();
	}

	/**
	 * Set the tags of a photo.
	 *
	 * @param SetPhotosTagsRequest $request
	 *
	 * @return void
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
	 * @param Move              $move
	 *
	 * @return void
	 *
	 * @throws LycheeException
	 */
	public function setAlbum(MovePhotosRequest $request, Move $move): void
	{
		$move->do($request->photos(), $request->album());
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
	 *
	 * @return SymfonyResponse
	 *
	 * @throws LycheeException
	 */
	public function getArchive(ArchivePhotosRequest $request): SymfonyResponse
	{
		return BaseArchive::resolve()->do($request->photos(), $request->sizeVariant());
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
