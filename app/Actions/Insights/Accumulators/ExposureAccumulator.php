<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Accumulators;

use App\Actions\Insights\Helpers\Distribution;
use App\Actions\Insights\Helpers\ExposureParser;
use App\Actions\Insights\PhotoRow;
use App\Http\Resources\Insights\DistributionData;
use App\Http\Resources\Insights\ExposureData;

/**
 * Exposure distributions (Feature 085, FR-085-13).
 *
 * ISO, focal length, shutter time and aperture are read from every item that
 * is not a video; video lengths from videos, rounded to whole seconds.
 */
final class ExposureAccumulator
{
	private Distribution $iso;
	private Distribution $focal;
	private Distribution $shutter;
	private Distribution $aperture;
	private Distribution $video_length;
	private float $total_video_duration = 0.0;

	public function __construct()
	{
		$this->iso = new Distribution();
		$this->focal = new Distribution();
		$this->shutter = new Distribution();
		$this->aperture = new Distribution();
		$this->video_length = new Distribution();
	}

	public function add(PhotoRow $row): void
	{
		if ($row->is_video) {
			$this->addVideo($row);

			return;
		}

		$this->iso->add(ExposureParser::iso($row->iso));
		$this->focal->add(ExposureParser::focal($row->focal));
		$this->shutter->add(ExposureParser::shutter($row->shutter));
		$this->aperture->add(ExposureParser::aperture($row->aperture));
	}

	public function toExposure(): ExposureData
	{
		return new ExposureData(
			iso: DistributionData::fromSummary($this->iso->summarise()),
			focal: DistributionData::fromSummary($this->focal->summarise()),
			shutter: DistributionData::fromSummary($this->shutter->summarise()),
			aperture: DistributionData::fromSummary($this->aperture->summarise()),
			video_length: DistributionData::fromSummary($this->video_length->summarise()),
			total_video_duration: $this->total_video_duration,
		);
	}

	private function addVideo(PhotoRow $row): void
	{
		$duration = ExposureParser::duration($row->duration);
		$this->video_length->add($duration === null ? null : round($duration));
		$this->total_video_duration += $duration ?? 0.0;
	}
}
