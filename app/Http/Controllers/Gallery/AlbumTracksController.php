<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Assets\Features;
use App\Enum\StorageDiskType;
use App\Http\Requests\Album\DeleteAlbumTrackRequest;
use App\Http\Requests\Album\RenameAlbumTrackRequest;
use App\Http\Requests\Album\SetAlbumTracksRequest;
use App\Http\Resources\Models\TrackResource;
use App\Jobs\UploadTrackToS3Job;
use App\Models\Track;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * v8-only multi-track management (Q-055-08: a new, standalone controller —
 * no existing controller in this codebase was an actual precedent to mirror).
 */
class AlbumTracksController extends Controller
{
	/**
	 * Batch-upload tracks for an album (FR-055-06).
	 *
	 * @return TrackResource[] the album's full, updated track list
	 */
	public function store(SetAlbumTracksRequest $request): array
	{
		$album = $request->album();
		$has_no_tracks = $album->tracks()->doesntExist();
		$use_s3 = Features::active('use-s3');

		foreach ($request->uploaded_files as $index => $file) {
			$track = new Track();
			$track->album_id = $album->id;
			$track->name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
			$track->file_name = $this->storeTrackFile($file);
			$track->is_primary = $has_no_tracks && $index === 0;
			$track->save();

			if ($use_s3) {
				UploadTrackToS3Job::dispatch($track, $album->owner_id);
			}
		}

		return $album->tracks()->get()->map(fn (Track $track) => new TrackResource($track))->all();
	}

	/**
	 * Rename exactly one track (FR-055-07).
	 */
	public function update(RenameAlbumTrackRequest $request): void
	{
		$track = $request->track;
		$track->name = $request->name;
		$track->save();
	}

	/**
	 * Delete exactly one track, promoting the next-oldest remaining track to
	 * primary if the deleted one was primary (FR-055-08, Q-055-10).
	 */
	public function destroy(DeleteAlbumTrackRequest $request): void
	{
		DB::transaction(function () use ($request): void {
			$track = $request->track;
			$was_primary = $track->is_primary;

			Storage::disk($track->disk->value)->delete($track->file_name);
			$track->delete();

			if ($was_primary) {
				$next = $request->album()->tracks()->first();
				if ($next !== null) {
					$next->is_primary = true;
					$next->save();
				}
			}
		});
	}

	/**
	 * Stores the uploaded track file on the local disk and returns its storage-relative path.
	 *
	 * @throws \Exception
	 */
	private function storeTrackFile(UploadedFile $file): string
	{
		$new_track_name = strtr(base64_encode(random_bytes(18)), '+/', '-_') . '.xml';
		Storage::disk(StorageDiskType::LOCAL->value)->putFileAs('tracks/', $file, $new_track_name);

		return 'tracks/' . $new_track_name;
	}
}
