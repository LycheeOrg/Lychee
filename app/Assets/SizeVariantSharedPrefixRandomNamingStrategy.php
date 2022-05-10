<?php

namespace App\Assets;

use App\Exceptions\InsufficientEntropyException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\Internal\MissingValueException;
use App\Image\FlysystemFile;
use App\Models\Photo;
use App\Models\SizeVariant;
use Safe\Exceptions\PcreException;

/**
 * A naming strategy for size variants which creates random names.
 *
 * Size variants for the same photo have a shared random prefix and are
 * distinguished by a suffix denoting their type, i.e. `-full`,
 * `-medium`, etc.
 */
class SizeVariantSharedPrefixRandomNamingStrategy extends SizeVariantBaseNamingStrategy
{
	/**
	 * Maps a size variant to its suffix.
	 */
	public const VARIANT_2_SUFFIX = [
		SizeVariant::THUMB => '-thumb',
		SizeVariant::THUMB2X => '-thumb2x',
		SizeVariant::SMALL => '-small',
		SizeVariant::SMALL2X => '-small2x',
		SizeVariant::MEDIUM => '-medium',
		SizeVariant::MEDIUM2X => '-medium2x',
		SizeVariant::ORIGINAL => '-full',
	];

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
	 * @var string a cached random base path for the shared prefix which is reset for every new photo
	 */
	protected string $cachedRndBasePath;

	/**
	 * @throws InsufficientEntropyException
	 */
	public function __construct()
	{
		$this->cachedRndBasePath = self::createRndBathPath();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws InsufficientEntropyException
	 */
	public function setPhoto(?Photo $photo): void
	{
		try {
			$this->photo = $photo;

			$origFile = $this->photo?->size_variants->getOriginal()?->getFile();
			if ($origFile) {
				$this->originalExtension = $origFile->getOriginalExtension();
				$existingRelPath = $origFile->getRelativePath();
				$matches = [];
				// Extract random bath path
				// As the naming strategy has been changed in the past, we must
				// not assume that an existing original size variant has already
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
				// As it is unspecified, how the beginning of the path is
				// reported, we must be prepared for an optional `/` or `./`
				// at the beginning.
				if (\Safe\preg_match(
						'#^\.?[/\\\\]?([0-9a-f]{2})[/\\\\]([0-9a-f]{2})[/\\\\]([0-9a-f]{' . (self::NAME_LENGTH - 4) . '})-full\.#i',
						$existingRelPath,
						$matches
					) === 1) {
					// If we have a match, we use the base path of the original
					// size variant
					$this->cachedRndBasePath = $matches[1] . '/' . $matches[2] . '/' . $matches[3];
				} else {
					// If we don't have a match, we create a new random base path.
					$this->cachedRndBasePath = self::createRndBathPath();
				}
			} else {
				$this->originalExtension = '';
				$this->cachedRndBasePath = self::createRndBathPath();
			}
		} catch (PcreException $e) {
			assert(false, new \AssertionError('regex could not be compiled; that should not happen for a statically coded regex', $e));
		}
	}

	/**
	 * Generates a short path for the designated size variant.
	 *
	 * @param int $sizeVariant the size variant
	 *
	 * @return FlysystemFile the file
	 *
	 * @throws InvalidSizeVariantException
	 * @throws IllegalOrderOfOperationException
	 * @throws MissingValueException
	 */
	public function createFile(int $sizeVariant): FlysystemFile
	{
		if (SizeVariant::ORIGINAL > $sizeVariant || $sizeVariant > SizeVariant::THUMB) {
			throw new InvalidSizeVariantException('invalid $sizeVariant = ' . $sizeVariant);
		}

		$extension = $this->generateExtension($sizeVariant);
		$relativePath =
			$this->cachedRndBasePath .
			self::VARIANT_2_SUFFIX[$sizeVariant] .
			$extension;

		// Flysystem does not support the semantics of `O_EXCL|O_CREAT` which
		// means "create a file if not exist and obtain an exclusive lock or fail".
		// This would be nice here to avoid accidental overwriting of a file,
		// but we must simply hope that 128bit randomness are sufficient.
		return new FlysystemFile(parent::getImageDisk(), $relativePath);
	}

	/**
	 * Draws a fresh random base path.
	 *
	 * @throws InsufficientEntropyException
	 */
	protected static function createRndBathPath(): string
	{
		try {
			$rndStr = bin2hex(random_bytes(self::NAME_LENGTH / 2));

			return
				\Safe\substr($rndStr, 0, 2) .
				'/' .
				\Safe\substr($rndStr, 2, 2) .
				'/' .
				\Safe\substr($rndStr, 4);
		} catch (\Exception $e) {
			throw new InsufficientEntropyException($e);
		}
	}
}
