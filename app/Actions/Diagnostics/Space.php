<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics;

use App\Metadata\DiskUsage;

class Space
{
	private DiskUsage $diskUsage;

	public function __construct(DiskUsage $diskUsage)
	{
		$this->diskUsage = $diskUsage;
	}

	/**
	 * get space used by Lychee.
	 *
	 * @return string[] array of messages
	 */
	public function get(): array
	{
		$infos = [];
		$infos[] = Diagnostics::line('Lychee total space:', $this->diskUsage->get_lychee_space());
		$infos[] = Diagnostics::line('Upload folder space:', $this->diskUsage->get_lychee_upload_space());
		$infos[] = Diagnostics::line('System total space:', $this->diskUsage->get_total_space());
		$infos[] = Diagnostics::line('System free space:', $this->diskUsage->get_free_space() . ' ('
			. $this->diskUsage->get_free_percent() . ')');

		return $infos;
	}
}
