<?php

namespace App\Contracts;

/**
 * @property ?string   $localBranch
 * @property ?string   $localHead
 * @property ?string   $remoteHead
 * @property false|int $countBehind
 * @property string    $age
 */
interface GitHubVersionControl
{
	/**
	 * Hydrate the GitData.
	 *
	 * @param bool $useCache
	 *
	 * @return void
	 */
	public function hydrate(bool $useCache = true): void;

	/**
	 * are we on Master?
	 *
	 * @return bool
	 */
	public function isMasterBranch(): bool;

	/**
	 * are we up to date.
	 *
	 * @return bool
	 */
	public function isUpToDate(): bool;

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