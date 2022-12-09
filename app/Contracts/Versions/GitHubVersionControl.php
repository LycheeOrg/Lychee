<?php

namespace App\Contracts\Versions;

/**
 * Interface to pack all the required information with regard to the Git version of Lychee.
 *
 * @property ?string $localBranch
 * @property ?string $localHead
 */
interface GitHubVersionControl extends VersionControl
{
	/**
	 * are we on Master?
	 *
	 * @return bool
	 */
	public function isMasterBranch(): bool;

	/**
	 * pretty print of the behind text.
	 *
	 * @return string
	 */
	public function getBehindTest(): string;

	/**
	 * Check if we can update.
	 *
	 * @return bool
	 */
	public function hasPermissions(): bool;
}