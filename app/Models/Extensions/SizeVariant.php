<?php

namespace App\Models\Extensions;

use App\Facades\Helpers;
use App\Models\Photo;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Class SizeVariant.
 *
 * Describes a size variant of a photo.
 */
class SizeVariant implements Arrayable, JsonSerializable
{
	const THUMBNAIL_DIM = 200;
	const THUMBNAIL2X_DIM = 400;
	const VARIANT_THUMB = 'thumb';
	const VARIANT_THUMB2X = 'thumb2x';
	const VARIANT_SMALL = 'small';
	const VARIANT_SMALL2X = 'small2x';
	const VARIANT_MEDIUM = 'medium';
	const VARIANT_MEDIUM2X = 'medium2x';
	const VARIANT_ORIGINAL = 'original';

	/**
	 * Maps a size variant to the path prefix (directory) where the file for that size variant is stored.
	 * Use this array to avoid the anti-pattern "magic constants" throughout the whole code.
	 */
	const VARIANT_2_PATH_PREFIX = [
		self::VARIANT_THUMB => 'thumb',
		self::VARIANT_THUMB2X => 'thumb',
		self::VARIANT_SMALL => 'small',
		self::VARIANT_SMALL2X => 'small',
		self::VARIANT_MEDIUM => 'medium',
		self::VARIANT_MEDIUM2X => 'medium',
		self::VARIANT_ORIGINAL => 'big',
	];

	/**
	 * @var string the type of this variant, possible values are
	 *             {@link SizeVariant::VARIANT_THUMB},
	 *             {@link SizeVariant::VARIANT_THUMB2X},
	 *             {@link SizeVariant::VARIANT_SMALL},
	 *             {@link SizeVariant::VARIANT_SMALL2X},
	 *             {@link SizeVariant::VARIANT_MEDIUM} and
	 *             {@link SizeVariant::VARIANT_MEDIUM2X}
	 */
	private string $type;

	/** @var Photo the Photo object this object is tied to */
	private Photo $photo;

	/**
	 * SizeVariant constructor.
	 *
	 * Use {@link createSizeVariant} instead.
	 * The factory method safely returns `null`, if the associated
	 * {@link \App\Models\Photo} object does not support the requested size
	 * variant.
	 *
	 * @param Photo  $photo the the Photo object this object is tied to
	 * @param string $type  the type of this variant, possible values are
	 *                      {@link SizeVariant::VARIANT_THUMB},
	 *                      {@link SizeVariant::VARIANT_THUMB2X},
	 *                      {@link SizeVariant::VARIANT_SMALL},
	 *                      {@link SizeVariant::VARIANT_SMALL2X},
	 *                      {@link SizeVariant::VARIANT_MEDIUM} and
	 *                      {@link SizeVariant::VARIANT_MEDIUM2X}
	 */
	protected function __construct(Photo $photo, string $type)
	{
		$this->photo = $photo;
		$this->type = $type;
	}

	/**
	 * Creates a SizeVariant of the given type for the indicated
	 * {@link \App\Models\Photo} object or returns `null`, if the indicated
	 * `Photo` object does not support the requested size variant.
	 *
	 * @param Photo  $photo the the Photo object this object is tied to
	 * @param string $type  the type of this variant, possible values are
	 *                      {@link SizeVariant::VARIANT_THUMB},
	 *                      {@link SizeVariant::VARIANT_THUMB2X},
	 *                      {@link SizeVariant::VARIANT_SMALL},
	 *                      {@link SizeVariant::VARIANT_SMALL2X},
	 *                      {@link SizeVariant::VARIANT_MEDIUM} and
	 *                      {@link SizeVariant::VARIANT_MEDIUM2X}
	 *
	 * @return SizeVariant|null A newly create object of this class or null
	 */
	public static function createSizeVariant(Photo $photo, string $type): ?SizeVariant
	{
		if (!$photo->thumb2x && $type === self::VARIANT_THUMB2X) {
			return null;
		}
		if (self::getWidthInternal($photo, $type) === 0 || self::getHeightInternal($photo, $type) === 0) {
			return null;
		}

		return new self($photo, $type);
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 */
	public function toArray(): array
	{
		return [
			'url' => $this->getUrl(),
			'width' => $this->getWidth(),
			'height' => $this->getHeight(),
		];
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 *
	 * @see SizeVariant::toArray()
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * @return string The type of this size variant, possible values are
	 *                {@link SizeVariant::VARIANT_THUMB2X},
	 *                {@link SizeVariant::VARIANT_SMALL},
	 *                {@link SizeVariant::VARIANT_SMALL2X},
	 *                {@link SizeVariant::VARIANT_MEDIUM} and
	 *                {@link SizeVariant::VARIANT_MEDIUM2X}
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Returns the relative path of the file as it needs to be input into
	 * methods of {@link \Illuminate\Support\Facades\Storage}.
	 *
	 * @return string the relative path of the file
	 */
	public function getRelativePath(): string
	{
		return self::VARIANT_2_PATH_PREFIX[$this->type] . '/' . $this->getFilename();
	}

	/**
	 * Returns the URL of the file as it is seen from a client's point of
	 * view.
	 * This is a convenient method and wraps the result of
	 * {@link getRelativePath()} into
	 * {@link \Illuminate\Support\Facades\Storage::url()}.
	 *
	 * @return string the url of the file
	 */
	public function getUrl(): string
	{
		return Storage::url($this->getRelativePath());
	}

	/**
	 * Returns the URL of the file as it is seen from a client's point of
	 * view.
	 * This is a convenient method and wraps the result of
	 * {@link getRelativePath()} into
	 * {@link \Illuminate\Support\Facades\Storage::path()}.
	 *
	 * @return string the url of the file
	 */
	public function getPath(): string
	{
		return Storage::path($this->getRelativePath());
	}

	/**
	 * Returns the width of this size variant.
	 *
	 * @return int the width
	 */
	public function getWidth(): int
	{
		return self::getWidthInternal($this->photo, $this->type);
	}

	/**
	 * Returns the width of the indicated size variant and photo.
	 *
	 * This is an internal helper method to get the width before an object
	 * of this class is actually constructed.
	 * If the size variant does not exist, zero is returned.
	 *
	 * @param Photo  $photo the underlying {@link \App\Models\Photo} object
	 * @param string $type  the name of the size variant
	 *
	 * @return int the width
	 */
	protected static function getWidthInternal(Photo $photo, string $type): int
	{
		switch ($type) {
			case self::VARIANT_THUMB:
				return self::THUMBNAIL_DIM;
			case self::VARIANT_THUMB2X:
				return self::THUMBNAIL2X_DIM;
			case self::VARIANT_ORIGINAL:
				return $photo->width;
			case self::VARIANT_SMALL:
			case self::VARIANT_SMALL2X:
			case self::VARIANT_MEDIUM:
			case self::VARIANT_MEDIUM2X:
				$width = $photo->{$type . '_width'};

				return $width ?: 0;
			default:
				throw new InvalidArgumentException('invalid size variant');
		}
	}

	/**
	 * Returns the height of this size variant.
	 *
	 * @return int the height
	 */
	public function getHeight(): int
	{
		return self::getHeightInternal($this->photo, $this->type);
	}

	/**
	 * Returns the height of the indicated size variant and photo.
	 *
	 * This is an internal helper method to get the height before an object
	 * of this class is actually constructed.
	 * If the size variant does not exist, zero is returned.
	 *
	 * @param Photo  $photo the underlying {@link \App\Models\Photo} object
	 * @param string $type  the name of the size variant
	 *
	 * @return int the height
	 */
	protected static function getHeightInternal(Photo $photo, string $type): int
	{
		switch ($type) {
			case self::VARIANT_THUMB:
				return self::THUMBNAIL_DIM;
			case self::VARIANT_THUMB2X:
				return self::THUMBNAIL2X_DIM;
			case self::VARIANT_ORIGINAL:
				return $photo->height;
			case self::VARIANT_SMALL:
			case self::VARIANT_SMALL2X:
			case self::VARIANT_MEDIUM:
			case self::VARIANT_MEDIUM2X:
				$height = $photo->{$type . '_height'};

				return $height ?: 0;
			default:
				throw new InvalidArgumentException('invalid size variant');
		}
	}

	/**
	 * Returns the base filename without any directory or alike.
	 *
	 * @return string the base filename
	 */
	public function getFilename(): string
	{
		$filename = $this->photo->filename;
		$thumbFilename = $this->photo->thumb_filename;
		if ($this->photo->isVideo() || $this->photo->type == 'raw') {
			$filename = $thumbFilename;
		}
		$filename2x = Helpers::ex2x($filename);
		$thumbFilename2x = Helpers::ex2x($thumbFilename);
		switch ($this->type) {
			case self::VARIANT_THUMB:
				return $thumbFilename;
			case self::VARIANT_THUMB2X:
				return $thumbFilename2x;
			case self::VARIANT_SMALL:
			case self::VARIANT_MEDIUM:
			case self::VARIANT_ORIGINAL:
				return $filename;
			case self::VARIANT_SMALL2X:
			case self::VARIANT_MEDIUM2X:
				return $filename2x;
			default:
				throw new InvalidArgumentException('invalid size variant');
		}
	}
}
