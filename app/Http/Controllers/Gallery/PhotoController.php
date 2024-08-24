<?php

namespace App\Http\Controllers\Gallery;

use App\Actions\Import\FromUrl;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\FileStatus;
use App\Factories\AlbumFactory;
use App\Http\Requests\Photo\FromUrlRequest;
use App\Http\Requests\Photo\GetPhotoRequest;
use App\Http\Requests\Photo\UploadPhotoRequest;
use App\Http\Resources\Editable\UploadMetaResource;
use App\Http\Resources\Models\PhotoResource;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\UploadedFile;
use App\Jobs\ProcessImageJob;
use App\Models\Configs;
use Illuminate\Routing\Controller;
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
}