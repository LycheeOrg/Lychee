<?php

namespace App\Actions\Diagnostics;

use App\Metadata\DiskUsage;

class Space
{
	use Line;

	/** @var LycheeVersion */
	private $diskUsage;

	public function __construct(DiskUsage $diskUsage)
	{
		$this->diskUsage = $diskUsage;
	}

	/**
	 * get space used by Lychee.
	 *
	 * @return array
	 */
	public function get(): array
	{
		$infos = [''];
		$infos[] = $this->line('Lychee total space:', $this->diskUsage->get_lychee_space());
		$infos[] = $this->line('Upload folder space:', $this->diskUsage->get_lychee_upload_space());
		$infos[] = $this->line('System total space:', $this->diskUsage->get_total_space());
		$infos[] = $this->line('System free space:', $this->diskUsage->get_free_space() . ' ('
			. $this->diskUsage->get_free_percent() . ')');

		return $infos;
	}
}
