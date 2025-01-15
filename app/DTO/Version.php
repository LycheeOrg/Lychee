<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use App\Exceptions\Internal\LycheeInvalidArgumentException;

class Version extends ArrayableDTO
{
	public function __construct(
		public int $major,
		public int $minor,
		public int $patch,
	) {
	}

	/**
	 * Converts a single integer into a three-part version.
	 *
	 * This method assumes that the minor and patch component always use two
	 * digits.
	 * This means, minor and patch levels below 10 are padded with a leading
	 * zero.
	 * Moreover, minor and patch level must not exceed 99.
	 * For example, "4.3.2" must be represented as 40302.
	 *
	 * @param int $version the version represented as a single integer
	 *
	 * @return self
	 *
	 * @throws LycheeInvalidArgumentException
	 */
	public static function createFromInt(int $version): self
	{
		if (10000 > $version || $version > 999999) {
			throw new LycheeInvalidArgumentException('unexpected version value');
		}

		return new self(intdiv($version, 10000), intdiv($version % 10000, 100), $version % 100);
	}

	/**
	 * Converts a string into a three-part version.
	 *
	 * The method supports two different formats.
	 * If the string contains exactly two dots (.) as separators, then the
	 * method splits the string at the dots and uses the resulting
	 * components as major, minor and patch level.
	 * Otherwise, the string must have exactly 6 characters.
	 * The string is split into three components with 2 characters each.
	 * Other formats are not supported.
	 *
	 * @param string $version
	 *
	 * @return self
	 *
	 * @throws LycheeInvalidArgumentException
	 */
	public static function createFromString(string $version): self
	{
		$version = trim($version);
		$exploded = explode('.', $version);
		if (count($exploded) === 3) {
			return new self(intval($exploded[0]), intval($exploded[1]), intval($exploded[2]));
		}
		if (strlen($version) === 5) {
			$version = '0' . $version;
		}
		if (strlen($version) === 6) {
			$exploded = str_split($version, 2);

			return new self(intval($exploded[0]), intval($exploded[1]), intval($exploded[2]));
		}
		throw new LycheeInvalidArgumentException('unexpected version value');
	}

	/**
	 * Converts the version into an integer which is suitable for comparison.
	 *
	 * The minor and patch level always have two digits, i.e. the version
	 * "4.3.1" is returned as 40301.
	 *
	 * @return int
	 */
	public function toInteger(): int
	{
		return 10000 * $this->major + 100 * $this->minor + $this->patch;
	}

	public function toString(): string
	{
		return $this->__toString();
	}

	public function __toString(): string
	{
		return $this->major . '.' . $this->minor . '.' . $this->patch;
	}
}
