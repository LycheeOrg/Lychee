<?php

namespace App\Assets;

use App\Exceptions\InsufficientEntropyException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Image\FlysystemFile;
use App\Models\Photo;
use App\Models\SizeVariant;
use Safe\Exceptions\PcreException;

/**
 * A size variant similar to the legacy system but with random base names.
 */
class SizeVariantGroupedRandomNamingStrategy extends SizeVariantBaseNamingStrategy
{
	/**
	 * Maps a size variant to the path prefix (directory) where the file for that size variant is stored.
	 */
	public const VARIANT_2_PATH_PREFIX = [
		SizeVariant::THUMB => 'thumb',
		SizeVariant::THUMB2X => 'thumb',
		SizeVariant::SMALL => 'small',
		SizeVariant::SMALL2X => 'small',
		SizeVariant::MEDIUM => 'medium',
		SizeVariant::MEDIUM2X => 'medium',
		SizeVariant::ORIGINAL => 'big',
	];

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
						'#^\.?[/\\\\]?(big|raw)[/\\\\]([0-9a-f]{' . self::NAME_LENGTH . '})\.#i',
						$existingRelPath,
						$matches
					) === 1) {
					// If we have a match, we use the base path of the original
					// size variant
					$this->cachedRndBasePath = $matches[1];
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
	 */
	public function createFile(int $sizeVariant): FlysystemFile
	{
		if (SizeVariant::ORIGINAL > $sizeVariant || $sizeVariant > SizeVariant::THUMB) {
			throw new InvalidSizeVariantException('invalid $sizeVariant = ' . $sizeVariant);
		}
		$directory = self::VARIANT_2_PATH_PREFIX[$sizeVariant] . '/';
		if ($sizeVariant === SizeVariant::ORIGINAL && $this->photo->isRaw()) {
			$directory = 'raw/';
		}
		$filename = $this->cachedRndBasePath;
		if ($sizeVariant === SizeVariant::MEDIUM2X ||
			$sizeVariant === SizeVariant::SMALL2X ||
			$sizeVariant === SizeVariant::THUMB2X) {
			$filename .= '@2x';
		}
		$extension = $this->generateExtension($sizeVariant);

		return new FlysystemFile(parent::getImageDisk(), $directory . $filename . $extension);
	}

	/**
	 * Draws a fresh random base path.
	 *
	 * @throws InsufficientEntropyException
	 */
	protected static function createRndBathPath(): string
	{
		try {
			return bin2hex(random_bytes(self::NAME_LENGTH / 2));
		} catch (\Exception $e) {
			throw new InsufficientEntropyException($e);
		}
	}
}
