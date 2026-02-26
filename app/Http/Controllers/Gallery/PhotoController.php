<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Import\FromUrl;
use App\Actions\Photo\Delete;
use App\Actions\Photo\MoveOrDuplicate;
use App\Actions\Photo\Rating;
use App\Actions\Photo\Rotate;
use App\Constants\FileSystem;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\FileStatus;
use App\Enum\SizeVariantType;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ConflictingPropertyException;
use App\Http\Requests\Photo\CopyPhotosRequest;
use App\Http\Requests\Photo\DeletePhotosRequest;
use App\Http\Requests\Photo\EditPhotoRequest;
use App\Http\Requests\Photo\FromUrlRequest;
use App\Http\Requests\Photo\GetPhotoAlbumsRequest;
use App\Http\Requests\Photo\MovePhotosRequest;
use App\Http\Requests\Photo\RenamePhotoRequest;
use App\Http\Requests\Photo\RotatePhotoRequest;
use App\Http\Requests\Photo\SetPhotoRatingRequest;
use App\Http\Requests\Photo\SetPhotosHighlightedRequest;
use App\Http\Requests\Photo\SetPhotosLicenseRequest;
use App\Http\Requests\Photo\SetPhotosTagsRequest;
use App\Http\Requests\Photo\UploadPhotoRequest;
use App\Http\Requests\Photo\WatermarkPhotoRequest;
use App\Http\Resources\Editable\UploadMetaResource;
use App\Http\Resources\Models\PhotoAlbumResource;
use App\Http\Resources\Models\PhotoResource;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\UploadedFile;
use App\Jobs\ExtractZip;
use App\Jobs\ProcessImageJob;
use App\Jobs\WatermarkerJob;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\Tag;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use LycheeVerify\Contract\VerifyInterface;

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

		return $this->process(
			$request->verify(),
			$request->configs(),
			$final,
			$request->album(),
			$request->file_last_modified_time(),
			$request->apply_watermark(),
			$meta);
	}

	private function process(
		VerifyInterface $verify,
		ConfigManager $config_manager,
		NativeLocalFile $final,
		?AbstractAlbum $album,
		?int $file_last_modified_time,
		?bool $apply_watermark,
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

		$is_zip = strtolower(pathinfo($meta->file_name, PATHINFO_EXTENSION)) === 'zip';
		$is_se = $verify->is_supporter();

		if ($is_se && $config_manager->getValueAsBool('extract_zip_on_upload') && $is_zip) {
			ExtractZip::dispatch($processable_file, $album?->get_id(), $file_last_modified_time);
			// We return DONE no matter what:
			// - if we are in sync mode, this will be executed after the job
			// - if we are in async mode, the job will be executed later, but we need to notify the front-end we are clear.
			$meta->stage = FileStatus::DONE;

			return $meta;
		}

		ProcessImageJob::dispatch($processable_file, $album, $file_last_modified_time, $apply_watermark);
		$meta->stage = config('queue.default') === 'sync' ? FileStatus::DONE : FileStatus::READY;

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

		return new PhotoResource(
			photo: $photo,
			album_id: $request->from_album()?->get_id(),
			should_downgrade_size_variants: !Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo])
		);
	}

	/**
	 * Set the is-highlighted attribute of the given photos.
	 */
	public function highlight(SetPhotosHighlightedRequest $request): void
	{
		foreach ($request->photos() as $photo) {
			$photo->is_highlighted = $request->isHighlighted();
			$photo->save();
		}
	}

	/**
	 * Set the rating for a photo.
	 *
	 * @param SetPhotoRatingRequest $request
	 * @param Rating                $rating
	 *
	 * @return PhotoResource
	 *
	 * @throws ConflictingPropertyException
	 */
	public function rate(SetPhotoRatingRequest $request, Rating $rating): PhotoResource
	{
		/** @var \App\Models\User $user */
		$user = Auth::user();

		$photo = $rating->do(
			$request->photo(),
			$user,
			$request->rating()
		);

		return new PhotoResource(
			photo: $photo,
			album_id: null,
			should_downgrade_size_variants: !Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo])
		);
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
		$delete->do($request->photoIds(), $request->from_id());
	}

	/**
	 * Given a photoID and a direction (+1: 90° clockwise, -1: 90° counterclockwise) rotate an image.
	 */
	public function rotate(RotatePhotoRequest $request): PhotoResource
	{
		if (!$request->configs()->getValueAsBool('editor_enabled')) {
			throw new ConfigurationException('support for rotation disabled by configuration');
		}

		$rotate_strategy = new Rotate($request->photo(), $request->direction());
		$photo = $rotate_strategy->do();

		return new PhotoResource(
			photo: $photo,
			album_id: $request->from_album()?->get_id(),
			should_downgrade_size_variants: !Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo])
		);
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
	 * Set the license for multiple photos.
	 */
	public function license(SetPhotosLicenseRequest $request): void
	{
		$license = $request->license();
		$photo_ids = collect($request->photoIds());
		DB::transaction(function () use ($photo_ids, $license): void {
			// Process photos in chunks of 100 to avoid memory issues
			$photo_ids->chunk(100)->each(function ($photo_id) use ($license): void {
				Photo::query()->whereIn('id', $photo_id)->update(['license' => $license->value]);
			});
		});
	}

	/**
	 * Get the albums containing a photo, filtered by user access.
	 *
	 * @param GetPhotoAlbumsRequest $request
	 *
	 * @return Collection<int,PhotoAlbumResource>
	 */
	public function albums(GetPhotoAlbumsRequest $request): Collection
	{
		$photo = $request->photo();
		$user = Auth::user();
		$album_policy = resolve(AlbumPolicy::class);

		return $photo->albums
			->filter(fn ($album) => $album_policy->canAccess($user, $album))
			->values()
			->map(fn ($album) => new PhotoAlbumResource($album));
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
		if ($size_variant->type === SizeVariantType::ORIGINAL && !request()->configs()->getValueAsBool('watermark_original')) {
			return false;
		}

		return !$size_variant->is_watermarked;
	}
}
