<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Import\FromUrl;
use App\Actions\Photo\Delete;
use App\Actions\Photo\MoveOrDuplicate;
use App\Actions\Photo\Rotate;
use App\Constants\FileSystem;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\FileStatus;
use App\Enum\SizeVariantType;
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
use App\Http\Requests\Photo\WatermarkPhotoRequest;
use App\Http\Resources\Editable\UploadMetaResource;
use App\Http\Resources\Models\PhotoResource;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\UploadedFile;
use App\Jobs\ExtractZip;
use App\Jobs\ProcessImageJob;
use App\Jobs\WatermarkerJob;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\Tag;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Controller responsible for fetching Photo Data.
 */
class PhotoController extends Controller
{
	/**
	 * Upload a picture.
	 */
	public function upload(UploadPhotoRequest $request): UploadMetaResource
	{
		$meta = $request->meta();
		$file = new UploadedFile($request->uploaded_file_chunk());

		// Set up meta data if not already present
		$meta->extension ??= '.' . pathinfo($meta->file_name, PATHINFO_EXTENSION);
		$meta->uuid_name ??= strtr(base64_encode(random_bytes(12)), '+/', '-_') . $meta->extension;

		$final = new NativeLocalFile(Storage::disk(FileSystem::IMAGE_UPLOAD)->path($meta->uuid_name));
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
		UploadMetaResource $meta,
	): UploadMetaResource {
		$processable_file = new ProcessableJobFile(
			$final->getOriginalExtension(),
			$meta->file_name
		);
		$processable_file->write($final->read());

		$final->close();
		$final->delete();
		$processable_file->close();
		// End of work-around

		if (Configs::getValueAsBool('extract_zip_on_upload') &&
			str_ends_with($processable_file->getPath(), '.zip')) {
			ExtractZip::dispatch($processable_file, $album->get_id(), $file_last_modified_time);
			$meta->stage = FileStatus::DONE;

			return $meta;
		}

		if (Configs::getValueAsBool('use_job_queues')) {
			ProcessImageJob::dispatch($processable_file, $album, $file_last_modified_time);
			$meta->stage = FileStatus::READY;

			return $meta;
		}

		$job = new ProcessImageJob($processable_file, $album, $file_last_modified_time);
		$job->handle(resolve(AlbumFactory::class));
		$meta->stage = FileStatus::DONE;

		return $meta;
	}

	/**
	 * Upload a picture from a URL.
	 */
	public function fromUrl(FromUrlRequest $request, FromUrl $from_url): string
	{
		$user_id = Auth::id();
		$from_url->do($request->urls(), $request->album(), $user_id);

		return 'success';
	}

	/**
	 * Update the info of a picture.
	 */
	public function update(EditPhotoRequest $request): PhotoResource
	{
		$photo = $request->photo();
		$photo->title = $request->title();
		$photo->description = $request->description();
		$photo->created_at = $request->uploadDate();

		$existing_tags = Tag::from($request->tags());
		$photo->tags()->sync($existing_tags->pluck('id'));
		$photo->load('tags');
		$photo->license = $request->license()->value;

		// if the request takenAt is null, then we set the initial value back.
		$photo->taken_at = $request->takenAt() ?? $photo->initial_taken_at;

		$photo->save();

		return new PhotoResource($photo, $request->from_album());
	}

	/**
	 * Set the is-starred attribute of the given photos.
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
	 */
	public function move(MovePhotosRequest $request, MoveOrDuplicate $move): void
	{
		$move->do(
			photos: $request->photos(),
			from_album: $request->from_album(),
			to_album: $request->album()
		);
	}

	/**
	 * Delete one or more photos.
	 */
	public function delete(DeletePhotosRequest $request, Delete $delete): void
	{
		$file_deleter = $delete->do($request->photoIds(), $request->from_id());
		App::terminating(fn () => $file_deleter->do());
	}

	/**
	 * Given a photoID and a direction (+1: 90° clockwise, -1: 90° counterclockwise) rotate an image.
	 */
	public function rotate(RotatePhotoRequest $request): PhotoResource
	{
		if (!Configs::getValueAsBool('editor_enabled')) {
			throw new ConfigurationException('support for rotation disabled by configuration');
		}

		$rotate_strategy = new Rotate($request->photo(), $request->direction());
		$photo = $rotate_strategy->do();

		return new PhotoResource($photo, $request->from_album());
	}

	/**
	 * Copy a photos to an album.
	 * Only the SQL entry is duplicated for space reason.
	 */
	public function copy(CopyPhotosRequest $request, MoveOrDuplicate $duplicate): void
	{
		$duplicate->do($request->photos(), $request->album(), $request->album());
	}

	/**
	 * Rename a photo.
	 */
	public function rename(RenamePhotoRequest $request): void
	{
		$photo = $request->photo();
		$photo->title = $request->title();
		$photo->save();
	}

	/**
	 * Set the tags of a photo.
	 */
	public function tags(SetPhotosTagsRequest $request): void
	{
		$tags = $request->tags();
		$photos = $request->photos();
		$photo_ids = $photos->pluck('id');

		// Fetch existing tags
		$existing_tags = Tag::from($tags);

		DB::transaction(function () use ($request, $existing_tags, $photo_ids): void {
			if ($request->shall_override) {
				// Delete existing associations for those photos ids if we override the tags
				DB::table('photos_tags')
					->whereIn('photo_id', $photo_ids)
					->delete();
			}

			// Associate the existing tags with the photos
			$existing_tags->each(function (Tag $tag) use ($photo_ids): void {
				$tag->photos()->syncWithoutDetaching($photo_ids);
			});
			DB::commit();
		});
	}

	/**
	 * Watermark all SizeVariants of a single photo.
	 *
	 * Dispatches a WatermarkerJob for each SizeVariant where short_path_watermarked is not set.
	 */
	public function watermark(WatermarkPhotoRequest $request): void
	{
		/** @var int $user_id */
		$user_id = Auth::id();

		// Get all photos from the request and process their size variants
		// Filter variants that need watermarking and dispatch jobs
		SizeVariant::query()
			->whereIn('photo_id', $request->photoIds())
			->whereNot('type', SizeVariantType::PLACEHOLDER)
			->get()
			->filter(fn (SizeVariant $v) => $this->shouldWatermark($v))
			->each(fn (SizeVariant $v) => WatermarkerJob::dispatch($v, $user_id));
	}

	private function shouldWatermark(?SizeVariant $size_variant): bool
	{
		if ($size_variant->type === SizeVariantType::ORIGINAL && !Configs::getValueAsBool('watermark_original')) {
			return false;
		}

		return !$size_variant->is_watermarked;
	}
}
