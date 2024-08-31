<?php

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
use App\Http\Requests\Photo\DeletePhotosRequest;
use App\Http\Requests\Photo\DuplicatePhotosRequest;
use App\Http\Requests\Photo\EditPhotoRequest;
use App\Http\Requests\Photo\FromUrlRequest;
use App\Http\Requests\Photo\GetPhotoRequest;
use App\Http\Requests\Photo\MovePhotosRequest;
use App\Http\Requests\Photo\RotatePhotoRequest;
use App\Http\Requests\Photo\SetPhotosStarredRequest;
use App\Http\Requests\Photo\UploadPhotoRequest;
use App\Http\Resources\Editable\UploadMetaResource;
use App\Http\Resources\Models\PhotoResource;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\UploadedFile;
use App\Jobs\ProcessImageJob;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Controller responsible for fetching Photo Data.
 */
class PhotoController extends Controller
{
	public const DISK_NAME = 'livewire-upload';

	/**
	 * Provided an albumID, returns the album.
	 *
	 * @param GetPhotoRequest $request
	 *
	 * @return PhotoResource
	 */
	public function get(GetPhotoRequest $request): PhotoResource
	{
		return new PhotoResource($request->photo());
	}

	public function upload(UploadPhotoRequest $request): UploadMetaResource
	{
		$meta = $request->meta();
		$file = new UploadedFile($request->uploaded_file_chunk());

		// Set up meta data if not already present
		$meta->extension ??= '.' . pathinfo($meta->file_name, PATHINFO_EXTENSION);
		$meta->uuid_name ??= strtr(base64_encode(random_bytes(12)), '+/', '-_') . $meta->extension;

		$final = new NativeLocalFile(Storage::disk(self::DISK_NAME)->path($meta->uuid_name));
		$final->append($file->read());
		// $file->delete();

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
			$final->getOriginalBasename()
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

	public function fromUrl(FromUrlRequest $request, FromUrl $fromUrl): string
	{
		/** @var int $userId */
		$userId = Auth::id();
		$fromUrl->do($request->urls(), $request->album(), $userId);

		return 'success';
	}

	public function update(EditPhotoRequest $request): PhotoResource
	{
		$photo = $request->photo();
		$photo->title = $request->title();
		$photo->description = $request->description();
		$photo->created_at = $request->uploadDate();
		$photo->tags = $request->tags();
		$photo->license = $request->license()->value;
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
	 * Duplicates a set of photos.
	 * Only the SQL entry is duplicated for space reason.
	 *
	 * @param DuplicatePhotosRequest $request
	 * @param Duplicate              $duplicate
	 *
	 * @return Collection<string|int, PhotoResource> the collection of duplicated photos
	 */
	public function duplicate(DuplicatePhotosRequest $request, Duplicate $duplicate): Collection
	{
		$duplicates = $duplicate->do($request->photos(), $request->album());

		return PhotoResource::collect($duplicates);
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
}