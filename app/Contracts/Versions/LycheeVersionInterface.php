<?php

namespace App\Contracts\Versions;

use App\DTO\LycheeChannelInfo;

interface LycheeVersionInterface
{
	/**
	 * Return the information with respect to Lychee.
	 *
	 * @return LycheeChannelInfo the version of lychee or null if no git data could be found
	 */
	public function getLycheeChannelInfo(): LycheeChannelInfo;

	/**
	 * Returns true if dev dependencies are found.
	 *
	 * @return bool
	 */
	public function isDev(): bool;

	/**
	 * Returns true if it is a release (zip) instead of git.
	 *
	 * @return bool
	 */
	public function isRelease(): bool;
}