<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Models;

use App\Contracts\Exceptions\LycheeException;
use App\Enum\SizeVariantType;
use App\Image\Files\FlysystemFile;
use App\Models\Photo;

/**
 * Interface SizeVariantNamingStrategy.
 */
abstract class AbstractSizeVariantNamingStrategy
{
	protected string $extension = '';
	protected ?Photo $photo = null;

	/**
	 * Sets the extension to be used for the size variants.
	 *
	 * {@link SizeVariantNamingStrategy::setPhoto()} also sets the
	 * extension, if the photo is linked to an original size variant.
	 * Hence, calling this method should only be necessary for creating new
	 * photos, if no size variant already exist.
	 *
	 * @param string $extension the extension
	 *
	 * @return void
	 */
	public function setExtension(string $extension): void
	{
		$this->extension = $extension;
	}

	/**
	 * Sets the photo for which names of size variants shall be generated.
	 *
	 * @param Photo|null $photo the photo whose size variants shall be named
	 *
	 * @return void
	 */
	public function setPhoto(?Photo $photo): void
	{
		$this->photo = $photo;
		$this->extension = '';
		if ($this->photo !== null && ($sv = $this->photo->size_variants->getOriginal()) !== null) {
			$this->extension = $sv->getFile()->getExtension();
		}
	}

	/**
	 * Creates a file for the designated size variant.
	 *
	 * @param SizeVariantType $sizeVariant the size variant
	 * @param bool            $isBackup    whether to create a backup file
	 *
	 * @return FlysystemFile the file
	 *
	 * @throws LycheeException
	 *
	 * @codeCoverageIgnore
	 */
	abstract public function createFile(SizeVariantType $sizeVariant, bool $isBackup = false): FlysystemFile;
}
