<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Import\FromUrl;
use App\Actions\Photo\Delete;
use App\Actions\Photo\Duplicate;
use App\Actions\Photo\Move;
use App\Actions\Photo\Rotate;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\FileStatus;
use App\Exceptions\ConfigurationException;
use App\Factories\AlbumFactory;
use App\Http\Requests\Photo\CopyPhotosRequest;
use App\Http\Requests\Photo\DeletePhotosRequest;
use App\Http\Requests\Photo\EditPhotoRequest;
use App\Http\Requests\Photo\FromUrlRequest;
use App\Http\Requests\Photo\MovePhotosRequest;
use App\Http\Requests\Photo\RenamePhotoRequest;
use App\Http\Requests\Photo\RotatePhotoRequest;
use App\Http\Requests\Photo\SetPhotosStarredRequest;
use App\Http\Requests\Photo\SetPhotosTagsRequest;
use App\Http\Requests\Photo\UploadPhotoRequest;
use App\Http\Resources\Editable\UploadMetaResource;
use App\Http\Resources\Models\PhotoResource;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\UploadedFile;
use App\Jobs\ProcessImageJob;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Controller responsible for fetching Photo Data.
 */
class PhotoController extends Controller
{
	public const DISK_NAME = 'image-upload';

	/**
	 * Upload a picture.
	 *
	 * @param UploadPhotoRequest $request
	 *
	 * @return UploadMetaResource
	 */
	public function upload(UploadPhotoRequest $request): UploadMetaResource
	{
		$meta = $request->meta();
		$file = new UploadedFile($request->uploaded_file_chunk());

		// Set up meta data if not already present
		$meta->extension ??= '.' . pathinfo($meta->file_name, PATHINFO_EXTENSION);
		$meta->uuid_name ??= strtr(base64_encode(random_bytes(12)), '+/', '-_') . $meta->extension;

		$final = new NativeLocalFile(Storage::disk(self::DISK_NAME)->path($meta->uuid_name));
		$final->append($file->read());

		if ($meta->chunk_number < $meta->total_chunks) {
			// Not the last chunk
			return $meta;
		}

		// Last chunk
		$meta->stage = FileStatus::PROCESSING;

		return $this->process($final, $request->album(), $request->file_last_modified_time(), $meta);
	}

	private function process(
		NativeLocalFile $final,
		?AbstractAlbum $album,
		?int $file_last_modified_time,
		UploadMetaResource $meta): UploadMetaResource
	{
		$processableFile = new ProcessableJobFile(
			$final->getOriginalExtension(),
			$meta->file_name
		);
		$processableFile->write($final->read());

		$final->close();
		$final->delete();
		$processableFile->close();
		// End of work-around

		if (Configs::getValueAsBool('use_job_queues')) {
			ProcessImageJob::dispatch($processableFile, $album, $file_last_modified_time);
			$meta->stage = FileStatus::READY;

			return $meta;
		}

		$job = new ProcessImageJob($processableFile, $album, $file_last_modified_time);
		$job->handle(resolve(AlbumFactory::class));
		$meta->stage = FileStatus::DONE;

		return $meta;
	}

	/**
	 * Upload a picture from a URL.
	 *
	 * @param FromUrlRequest $request
	 * @param FromUrl        $fromUrl
	 *
	 * @return string
	 */
	public function fromUrl(FromUrlRequest $request, FromUrl $fromUrl): string
	{
		/** @var int $userId */
		$userId = Auth::id();
		$fromUrl->do($request->urls(), $request->album(), $userId);

		return 'success';
	}

	/**
	 * Update the info of a picture.
	 *
	 * @param EditPhotoRequest $request
	 *
	 * @return PhotoResource
	 */
	public function update(EditPhotoRequest $request): PhotoResource
	{
		$photo = $request->photo();
		$photo->title = $request->title();
		$photo->description = $request->description();
		$photo->created_at = $request->uploadDate();
		$photo->tags = $request->tags();
		$photo->license = $request->license()->value;

		// if the request takenAt is null, then we set the initial value back.
		$photo->taken_at = $request->takenAt() ?? $photo->initial_taken_at;

		$photo->save();

		return PhotoResource::fromModel($photo);
	}

	/**
	 * Set the is-starred attribute of the given photos.
	 *
	 * @param SetPhotosStarredRequest $request
	 *
	 * @return void
	 */
	public function star(SetPhotosStarredRequest $request): void
	{
		foreach ($request->photos() as $photo) {
			$photo->is_starred = $request->isStarred();
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
	 */
	public function move(MovePhotosRequest $request, Move $move): void
	{
		$move->do($request->photos(), $request->album());
	}

	/**
	 * Delete one or more photos.
	 *
	 * @param DeletePhotosRequest $request
	 * @param Delete              $delete
	 *
	 * @return void
	 */
	public function delete(DeletePhotosRequest $request, Delete $delete): void
	{
		$fileDeleter = $delete->do($request->photoIds());
		App::terminating(fn () => $fileDeleter->do());
	}

	/**
	 * Given a photoID and a direction (+1: 90° clockwise, -1: 90° counterclockwise) rotate an image.
	 *
	 * @param RotatePhotoRequest $request
	 *
	 * @return PhotoResource
	 */
	public function rotate(RotatePhotoRequest $request): PhotoResource
	{
		if (!Configs::getValueAsBool('editor_enabled')) {
			throw new ConfigurationException('support for rotation disabled by configuration');
		}

		$rotateStrategy = new Rotate($request->photo(), $request->direction());
		$photo = $rotateStrategy->do();

		return PhotoResource::fromModel($photo);
	}

	/**
	 * Copy a photos to an album.
	 * Only the SQL entry is duplicated for space reason.
	 *
	 * @param CopyPhotosRequest $request
	 * @param Duplicate         $duplicate
	 *
	 * @return void
	 */
	public function copy(CopyPhotosRequest $request, Duplicate $duplicate): void
	{
		$duplicate->do($request->photos(), $request->album());
	}

	/**
	 * Rename a photo.
	 *
	 * @param RenamePhotoRequest $request
	 *
	 * @return void
	 */
	public function rename(RenamePhotoRequest $request): void
	{
		$photo = $request->photo();
		$photo->title = $request->title;
		$photo->save();
	}

	/**
	 * Set the tags of a photo.
	 *
	 * @param SetPhotosTagsRequest $request
	 *
	 * @return void
	 */
	public function tags(SetPhotosTagsRequest $request): void
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
}