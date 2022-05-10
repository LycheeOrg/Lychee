<?php

namespace App\Assets;

use App\Exceptions\InsufficientEntropyException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Image\FlysystemFile;
use App\Models\SizeVariant;

/**
 * A naming strategy for size variants which creates fully random names.
 */
class SizeVariantFullyRandomNamingStrategy extends SizeVariantBaseNamingStrategy
{
	/**
	 * The length of the random file name without file extension.
	 *
	 * The file name is a random byte sequence encoded as a string with
	 * hexadecimal digits.
	 * 32 characters means 32chr * 4bit/chr = 128 bit randomness.
	 * 128 bit of randomness are considered sufficient to only
	 * allow for a small chance to generate a clash of filenames.
	 * We use a hexadecimal encoding (instead of an encoding in Base64),
	 * because some filesystems are case-insensitive, and we want to stay
	 * cross-platform compatible.
	 * Otherwise, a Base64-encoding would be more efficient in space and
	 * runtime.
	 * The value must be dividable by 2.
	 *
	 * @var int
	 */
	public const NAME_LENGTH = 32;

	/**
	 * {@inheritDoc}
	 */
	public function createFile(int $sizeVariant): FlysystemFile
	{
		if (SizeVariant::ORIGINAL > $sizeVariant || $sizeVariant > SizeVariant::THUMB) {
			throw new InvalidSizeVariantException('invalid $sizeVariant = ' . $sizeVariant);
		}

		try {
			$rndStr = bin2hex(random_bytes(self::NAME_LENGTH / 2));
		} catch (\Exception $e) {
			throw new InsufficientEntropyException($e);
		}

		// We use the first four hex-digits to create a two-level deep
		// directory hierarchy, e.g. `xx/yy/zzzzzzzzzzzzzzzzzzzzzzzzzzzz.<ext>`.
		$extension = $this->generateExtension($sizeVariant);
		$relativePath =
			\Safe\substr($rndStr, 0, 2) .
			'/' .
			\Safe\substr($rndStr, 2, 2) .
			'/' .
			\Safe\substr($rndStr, 4) .
			$extension;

		// Flysystem does not support the semantics of `O_EXCL|O_CREAT` which
		// means "create a file if not exist and obtain an exclusive lock or fail".
		// This would be nice here to avoid accidental overwriting of a file,
		// but we must simply hope that 128bit randomness are sufficient.
		return new FlysystemFile(parent::getImageDisk(), $relativePath);
	}
}
