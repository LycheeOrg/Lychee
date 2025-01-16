<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticStringPipe;

/**
 * Which version of Lychee are we using?
 */
class DockerVersionInfo implements DiagnosticStringPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$docker = 'false';
		if ($this->isDocker()) {
			$docker = match (true) {
				$this->isLinuxServer() => 'linuxserver.io',
				$this->isLycheeOrg() => 'lycheeorg',
				default => 'custom',
			};
		}

		$data[] = Diagnostics::line('Docker:', $docker);

		return $next($data);
	}

	/**
	 * Check if we are running in Docker.
	 *
	 * @return bool
	 */
	public function isDocker(): bool
	{
		return is_file('/.dockerenv');
	}

	/**
	 * Check if we are running in Docker.
	 *
	 * @return bool
	 */
	private function isLinuxServer(): bool
	{
		return is_file('/build_version');
	}

	/**
	 * Check if we are running in Docker.
	 *
	 * @return bool
	 */
	private function isLycheeOrg(): bool
	{
		return is_file(base_path('/docker_target'));
	}
}
