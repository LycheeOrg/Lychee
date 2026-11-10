<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Accumulators;

use App\Actions\Insights\PhotoRow;
use App\Http\Resources\Insights\OverviewData;
use App\Http\Resources\Insights\PeopleData;
use App\Http\Resources\Insights\PlacesData;
use App\Http\Resources\Insights\StorageData;

/**
 * Counts for the overview, storage, people and places sections
 * (Feature 085, FR-085-06 … FR-085-08, FR-085-14).
 */
final class OverviewAccumulator
{
	private int $total = 0;
	private int $photos = 0;
	private int $videos = 0;
	private int $highlighted = 0;
	private int $without_album = 0;
	private int $located = 0;
	private int $total_size = 0;
	private int $size_unknown = 0;
	private int $photo_size = 0;
	private int $photos_with_size = 0;
	private int $video_size = 0;
	private int $videos_with_size = 0;
	private int $photos_with_people = 0;
	private int $faces = 0;

	public function add(PhotoRow $row): void
	{
		$this->total++;
		$this->photos += intval($row->is_image);
		$this->videos += intval($row->is_video);
		$this->highlighted += intval($row->is_highlighted);
		$this->without_album += intval(!$row->in_album);
		$this->located += intval($row->is_located);
		$this->photos_with_people += intval($row->face_count > 0);
		$this->faces += $row->face_count;
		$this->addSize($row);
	}

	public function toOverview(int $albums): OverviewData
	{
		return new OverviewData(
			total: $this->total,
			photos: $this->photos,
			videos: $this->videos,
			others: $this->total - $this->photos - $this->videos,
			highlighted: $this->highlighted,
			albums: $albums,
			photos_without_album: $this->without_album,
		);
	}

	public function toStorage(): StorageData
	{
		return new StorageData(
			total_size: $this->total_size,
			size_unknown: $this->size_unknown,
			average_photo_size: self::average($this->photo_size, $this->photos_with_size),
			average_video_size: self::average($this->video_size, $this->videos_with_size),
		);
	}

	public function toPeople(bool $has_faces, int $people): PeopleData
	{
		return new PeopleData(
			has_faces: $has_faces,
			photos_with_people: $this->photos_with_people,
			people: $people,
			faces: $this->faces,
			faces_per_photo: $this->photos_with_people > 0 ? $this->faces / $this->photos_with_people : null,
		);
	}

	public function toPlaces(): PlacesData
	{
		return new PlacesData(
			located: $this->located,
			share: $this->total > 0 ? $this->located / $this->total : 0.0,
		);
	}

	private function addSize(PhotoRow $row): void
	{
		if ($row->filesize <= 0) {
			$this->size_unknown++;

			return;
		}

		$this->total_size += $row->filesize;
		$this->photo_size += $row->is_image ? $row->filesize : 0;
		$this->photos_with_size += intval($row->is_image);
		$this->video_size += $row->is_video ? $row->filesize : 0;
		$this->videos_with_size += intval($row->is_video);
	}

	private static function average(int $sum, int $count): ?int
	{
		return $count > 0 ? intval(round($sum / $count)) : null;
	}
}
