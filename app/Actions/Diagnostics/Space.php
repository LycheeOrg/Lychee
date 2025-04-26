<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics;

use App\Metadata\DiskUsage;

class Space
{
	public function __construct(private DiskUsage $disk_usage)
	{
	}

	/**
	 * get space used by Lychee.
	 *
	 * @return string[] array of messages
	 */
	public function get(): array
	{
		$infos = [];
		$infos[] = Diagnostics::line('Lychee total space:', $this->disk_usage->get_lychee_space());
		$infos[] = Diagnostics::line('Upload folder space:', $this->disk_usage->get_lychee_upload_space());
		$infos[] = Diagnostics::line('System total space:', $this->disk_usage->get_total_space());
		$infos[] = Diagnostics::line('System free space:', $this->disk_usage->get_free_space() . ' ('
			. $this->disk_usage->get_free_percent() . ')');

		return $infos;
	}
}
