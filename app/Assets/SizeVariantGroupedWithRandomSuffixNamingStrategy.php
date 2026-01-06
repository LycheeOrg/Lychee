<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Assets;

use App\DTO\CreateSizeVariantFlags;
use App\Enum\SizeVariantType;
use App\Exceptions\InsufficientEntropyException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Image\Files\FlysystemFile;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Safe\Exceptions\PcreException;

/**
 * A naming strategy for size variants which groups size variants by their
 * type into top-level directories, use a random file name and two levels
 * of subdirectories between the top-level directory and the file.
 *
 * Size variants which belong to the same photo share the same random
 * end section.
 */
class SizeVariantGroupedWithRandomSuffixNamingStrategy extends BaseSizeVariantNamingStrategy
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
	 * A cached random path which is reset for every new photo and represents
	 * the "middle" portion of the file path, i.e. without the top directory
	 * (`original`, `medium`, etc.) and without the file extension.
	 *
	 * The string has the pattern `[0-9a-f]{2}/[0-9a-f]{2}/[0-9a-f]{28}`.
	 *
	 * @var string
	 */
	protected string $cachedRndMiddlePath;

	/**
	 * @throws InsufficientEntropyException
	 */
	public function __construct()
	{
		$this->cachedRndMiddlePath = self::createRndMiddlePath();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws InsufficientEntropyException
	 */
	public function setPhoto(?Photo $photo): void
	{
		try {
			parent::setPhoto($photo);

			$orig_file = $this->photo?->size_variants->getOriginal()?->getFile();
			$cached_rnd_middle_path = null;
			if ($orig_file !== null) {
				$existing_rel_path = $orig_file->getRelativePath();
				$cached_rnd_middle_path = $this->getRndMiddleFromPath($existing_rel_path, SizeVariantType::ORIGINAL);
			}
			$this->cachedRndMiddlePath = $cached_rnd_middle_path ?? self::createRndMiddlePath();
			// @codeCoverageIgnoreStart
		} catch (PcreException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @param string $existing_rel_path
	 *
	 * @return string|null
	 *
	 * @throws PcreException
	 */
	protected function getRndMiddleFromPath(string $existing_rel_path, SizeVariantType $type): ?string
	{
		$matches = [];
		// Extract random base path
		// As the naming strategy has been changed in the past, we must
		// not assume that an existing original size variant already has
		// the right pattern.
		//
		// In order to handle UNIX and Windows directory separators,
		// we must match for `/` and `\`.
		// Note the funny number of four (!) backslashes inside the
		// character class `[/\\\\]`.
		// This is not an error!
		// PHP uses the backslash itself to escape character inside
		// a string.
		// So `\\` becomes one backslash on the PHP level.
		// The POSIX regex engine uses backslash for escaping, too,
		// so we need four.
		//
		// As it is unspecified how the beginning of the path is
		// reported, we must be prepared for an optional `/` or `./`
		// at the beginning.
		if (\Safe\preg_match(
			'#^\.?[/\\\\]?' .
			$type->name() . '[/\\\\]' .
			'([0-9a-f]{2})[/\\\\]' .
			'([0-9a-f]{2})[/\\\\]' .
			'([0-9a-f]{' . (self::NAME_LENGTH - 4) . '})\.#i',
			$existing_rel_path,
			$matches
		) === 1) {
			// If we have a match, we use the middle path of the original
			// size variant
			return $matches[1] . DIRECTORY_SEPARATOR . $matches[2] . DIRECTORY_SEPARATOR . $matches[3];
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFile(SizeVariantType $size_variant, CreateSizeVariantFlags $flags): FlysystemFile
	{
		$relative_path =
			$size_variant->name() . DIRECTORY_SEPARATOR .
			$this->cachedRndMiddlePath .
			$flags->get_suffix() .
			$this->generateExtension($size_variant);

		return new FlysystemFile(Storage::disk($flags->disk->value), $relative_path);
	}

	/**
	 * Draws a fresh random base path.
	 *
	 * @throws InsufficientEntropyException
	 */
	protected static function createRndMiddlePath(): string
	{
		try {
			$rnd_str = bin2hex(random_bytes(self::NAME_LENGTH / 2));

			return
				substr($rnd_str, 0, 2) .
				DIRECTORY_SEPARATOR .
				substr($rnd_str, 2, 2) .
				DIRECTORY_SEPARATOR .
				substr($rnd_str, 4);
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			throw new InsufficientEntropyException($e);
		}
		// @codeCoverageIgnoreEnd
	}
}
